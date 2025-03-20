import { PluginSidebar, PluginSidebarMoreMenuItem } from "@wordpress/editor";
import { registerPlugin } from "@wordpress/plugins";
import apiFetch from "@wordpress/api-fetch";
import { select, useSelect, useDispatch } from "@wordpress/data";
import RobotIcon from './RobotIcon'
import {
  Button,
  DropdownMenu,
  Flex,
  FlexBlock,
  Modal,
  NavigableMenu,
  Panel,
  PanelBody,
  PanelRow,
  RangeControl,
  SelectControl,
  Spinner,
  SnackbarList,
  TextareaControl,
  __experimentalConfirmDialog as ConfirmDialog,
} from "@wordpress/components";
import * as icons from "@wordpress/icons";
import { pencil, settings, trash } from "@wordpress/icons";
import { useEffect, useState } from "@wordpress/element";

const options = {
  baseEndpoint: "/editor-ai/v1",
  apiUseCases: "/use-cases",
  apiPlayground: "/playground",
  taskName: "editor-ai",
  modelParams: {
    model: "CLAUDE_3_5_SONNET",
    maxTokens: 3000,
    temperature: 0.9,
    topP: 0.759,
    topK: 250,
  },
  timeout: 60,
  prompts: null,
  models: [
    { label: "Amazon Nova Pro", value: "AMAZON_NOVA_PRO" },
    { label: "Amazon Nova Lite", value: "AMAZON_NOVA_LITE" },
    { label: "Amazon Nova Micro", value: "AMAZON_NOVA_MICRO" },
    { label: "Claude 3.5 Sonnet", value: "CLAUDE_3_5_SONNET" },
    { label: "Claude 3 Sonnet", value: "CLAUDE_3_SONNET" },
    { label: "Claude 3 Haiku", value: "CLAUDE_3_HAIKU" },
  ],
  forbiddenBlocks: [
    "core/html",
    "core/more",
    "core/nextpage",
    "core/spacer",
    "core/separator",
    "core/audio",
    "core/video",
    "core/file",
    "core/image",
    "core/gallery",
  ],
};

function PluginComponent() {
  const [isOpen, setOpen] = useState(false);
  const [isOpenSettings, setOpenSettings] = useState(false);
  const [modelParams, setModelParams] = useState(options.modelParams);
  const [loading, setLoading] = useState(false);
  const [useCases, setUseCases] = useState([]);
  const [snacks, setSnacks] = useState([]);
  const [applyDialog, setApplyDialog] = useState(false);
  const [promptAi, setPromptAi] = useState("");
  const [contextAi, setContextAi] = useState("");
  const [responseAi, setResponseAi] = useState("");
  const { removeBlocks, insertBlocks } = useDispatch("core/block-editor");
  const userCan = useSelect((select) =>
    select("core").canUser("create", "users"),
  );
  const closeModal = () => setOpen(false);

  function openUseCase(useCase) {
    if (useCase.prompt) setPromptAi(useCase.prompt);
    if (useCase.context) setContextAi(useCase.context);
    if (useCase.contextContent) copyContent();
    setOpen(true);
  }
  async function loadUseCases() {
    const useCases = await apiFetch({
      method: "GET",
      path: `${options.baseEndpoint}${options.apiUseCases}`,
    });
    if (!useCases.length) {
      addSnack({
        id: "RESPONSE_LOAD_USE_CASES",
        content: "Falha ao carregar os casos de uso!",
        spokenMessage: "Falha ao carregar os casos de uso!",
        explicitDismiss: true,
        actions: [],
      });
      return;
    }
    setUseCases(useCases);
  }
  function toggleSettings() {
    setOpenSettings(!isOpenSettings);
  }
  function setModelParam(key, value) {
    setModelParams((state) => ({ ...state, [key]: value }));
  }
  function addResponseAi(value) {
    setResponseAi(value);
  }
  function addSnack(snack) {
    if (snacks.find((s) => s.id === snack.id)) {
      setSnacks((state) => state.filter((s) => s.id !== snack.id));
    }
    if (!snack.onDismiss && !snack.onRemove) {
      snack.onDismiss = function () {
        setSnacks((state) => state.filter((s) => s.id !== snack.id));
      };
      snack.onRemove = function () {
        setSnacks((state) => state.filter((s) => s.id !== snack.id));
      };
    }
    console.log("Snackbar addSnack", snack);
    setSnacks((state) => [...state, snack]);
  }
  async function sendToAI() {
    setLoading(true);

    const payload = {
      site: options.taskSite,
      task: options.taskName,
      input: {
        model: modelParams.model,
        prompt: `- Retorne o resultado sempre e plain text, sem HTML, Markdown ou JSON!\n\n${promptAi}`,
        context: contextAi,
        max_tokens: modelParams.maxTokens,
        temperature: modelParams.temperature || 0.5,
        top_p: modelParams.topP || 0.5,
        top_k: modelParams.topK || 250,
      },
    };

    console.log("Editor AI - Payload:", payload);

    const response = await apiFetch({
      method: "POST",
      path: `${options.baseEndpoint}${options.apiPlayground}`,
      data: payload,
    });

    if (response.success === true) {
      addResponseAi(response.result);
      addSnack({
        id: "RESPONSE_AI_SUCCESS",
        content: "Conteúdo gerado com sucesso!",
        spokenMessage: "Conteúdo gerado com sucesso!",
        // explicitDismiss: true,
        actions: [],
      });
    }
    setLoading(false);
  }
  function getPostTitle() {
    return select("core/editor").getEditedPostAttribute("title");
  }
  async function copyResponse() {
    if (!navigator.clipboard) return;
    await navigator.clipboard.writeText(responseAi);
    addSnack({
      id: "COPY_RESPONSE",
      content: "Copiado para a área de transferência",
      spokenMessage: "Copiado para a área de transferência",
      // explicitDismiss: true,
      actions: [],
    });
  }
  function copyContent() {
    const title = getPostTitle();
    const blocks = getContentBlocks();
    const content = convertBlocksToText(blocks);
    console.log({ title, content });
    setContextAi(`Título: ${title}\n\n${content}`);
    addSnack({
      id: "COPY_CONTEXT_AI",
      content: "Conteúdo do post copiado para o contexto",
      spokenMessage: "Conteúdo do post copiado para o contexto",
      // explicitDismiss: true,
      actions: [],
    });
  }
  function applyContent() {
    const blocks = getContentBlocks();
    const clientIds = getClientIdFromBlocks(blocks);
    const newBlocks = parseToBlocks(responseAi);
    removeBlocks(clientIds);
    insertBlocks(newBlocks);
    closeModal();
  }
  function applyAfterContent() {
    const newBlocks = parseToBlocks(responseAi);
    insertBlocks(newBlocks);
    closeModal();
  }
  function parseToBlocks(content) {
    const block = wp.blocks.parse(content);
    return convertToBlock(block.shift());
  }
  function convertToBlock(block) {
    return wp.blocks.rawHandler({ HTML: wp.blocks.getBlockContent(block) });
  }
  function isBlockAllowed(block) {
    return !options.forbiddenBlocks.includes(block.name);
  }
  function getContentBlocks() {
    return select("core/block-editor").getBlocks();
  }
  function convertBlocksToText(blocks) {
    return blocks
      .filter((block) => isBlockAllowed(block))
      .map((block) => convertBlockToText(block))
      .join("\n\n")
      .trim();
  }
  function convertBlockToText(block) {
    if (block.innerBlocks.length) return convertBlocksToText(block.innerBlocks);
    try {
      return block.attributes.content.text;
    } catch (error) {
      console.error("Editor AI - Error convertBlockToText", { block, error });
    }
  }
  function getClientIdFromBlocks(blocks) {
    return blocks.map((block) => block.clientId);
  }

  useEffect(() => {
    loadUseCases();
  }, []);

  return (
    <>
      <style>
        {`
          .sidebar-editor-ai .components-dropdown-menu {
            display: flex;
            flex-direction: column;
            flex-wrap: wrap;
          }
          .sidebar-editor-ai .components-dropdown-menu .components-button {
              box-shadow: inset 0 0 0 1px var(--wp-components-color-accent, var(--wp-admin-theme-color, #3858e9));
              color: var(--wp-components-color-accent, var(--wp-admin-theme-color, #3858e9));
          }
          .components-modal__screen-overlay:has(.modal-editor-ai) {
            background-color: #0162FBaa;
          }
          .modal-editor-ai .components-modal__content {
            display: flex;
            flex-direction: column;
          }
          .modal-editor-ai .components-modal__header + div {
            position: relative;
            flex: 1;
            display: flex;
            flex-direction: column;
          }
          .modal-edit-ai__loading {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
          }
          .modal-editor-ai__flex {
            position: relative;
            flex: 1;
            display: flex;
            align-items: stretch;
          }
          .modal-editor-ai__settings {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 280px;
          }
          .modal-editor-ai__item {
            flex: 1;
            display: flex;
            flex-direction: column;
          }
          .modal-editor-ai__textarea-control {
            flex: 1;
            display: flex;
            flex-direction: column;
          }
          .modal-editor-ai__textarea-control .components-base-control__field {
            flex: 1;
            display: flex;
            flex-direction: column;
          }
          .modal-editor-ai__textarea-control .components-textarea-control__input {
            flex: 1;
          }
          .modal-editor-ai__menu {
            display: flex;
            gap: .25rem;
            padding: .25rem;
            background-color: #f6f6f6;
            border: 1px solid #949494;
            border-radius: 2px;
          }
          .modal-editor-ai__menu .components-button.is-secondary {
            background-color: #fff;
          }
          .modal-editor-ai .components-snackbar-list {
            bottom: 60px;
            left: 6px;
          }
        `}
      </style>
      <PluginSidebarMoreMenuItem target="sidebar-editor-ai">
        Inteligência Artificial
      </PluginSidebarMoreMenuItem>
      <PluginSidebar
        name="sidebar-editor-ai"
        title="Editor AI"
        className="sidebar-editor-ai"
      >
        <PanelBody title="Ferramentas de Geração">
          <p>
            Use a inteligência artificial como uma aliada para facilitar o seu
            trabalho e ser mais produtivo.
          </p>
          <Flex direction="column">
            {!!useCases.length && useCases.map((useCase) => (
              <Button
                variant={useCase?.buttonVariant || 'secondary'}
                icon={useCase?.buttonIcon && icons[useCase.buttonIcon] ? icons[useCase.buttonIcon] : pencil}
                onClick={() => openUseCase(useCase)}
              >
                {useCase.buttonLabel}
              </Button>
            ))}
            {/* <DropdownMenu
              text="Expandir conteúdo"
              label="Gerar ideias de novos conteúdos"
              icon={plus}
              controls={[
                {
                  title: useCases.generateFollowup.buttonLabel,
                  onClick: useCases.generateFollowup.open,
                },
                {
                  title: useCases.generateExpansion.buttonLabel,
                  onClick: useCases.generateExpansion.open,
                },
              ]}
            /> */}
          </Flex>
        </PanelBody>
      </PluginSidebar>
      {isOpen && (
        <Modal
          title="Editor AI – Playground"
          size="fill"
          className="modal-editor-ai"
          onRequestClose={closeModal}
          shouldCloseOnEscc={false}
          shouldCloseOnClickOutside={false}
          headerActions={
            userCan && (
              <Button
                icon={settings}
                label="Configurações"
                size="compact"
                onClick={toggleSettings}
                variant={isOpenSettings ? "primary" : undefined}
              />
            )
          }
        >
          <Flex
            justify="stretch"
            align="stretch"
            className="modal-editor-ai__flex"
          >
            {loading && (
              <div className="modal-edit-ai__loading">
                <Spinner
                  style={{
                    height: "calc(4px * 20)",
                    width: "calc(4px * 20)",
                  }}
                />
              </div>
            )}
            {isOpenSettings && (
              <FlexBlock className="modal-editor-ai__settings">
                <Panel>
                  <PanelBody title="Configurações" icon={settings}>
                    <PanelRow>
                      <SelectControl
                        label="Modelo"
                        help="Modelo de IA a ser utilizado"
                        value={modelParams.model}
                        options={options.models}
                        onChange={(value) => setModelParam("model", value)}
                      />
                    </PanelRow>
                    <PanelRow>
                      <RangeControl
                        label="Max Tokens"
                        help="Tamanho máximo da resposta"
                        initialPosition={modelParams.maxTokens}
                        value={modelParams.maxTokens}
                        max={10000}
                        min={100}
                        onChange={(value) => setModelParam("maxTokens", value)}
                      />
                    </PanelRow>
                    <PanelRow>
                      <RangeControl
                        label="Temperatura"
                        help="Aleatoriedade (criatividade) na resposta"
                        initialPosition={modelParams.temperature}
                        value={modelParams.temperature}
                        max={1}
                        min={0}
                        step={0.05}
                        onChange={(value) =>
                          setModelParam("temperature", value)
                        }
                      />
                    </PanelRow>
                    <PanelRow>
                      <RangeControl
                        label="Top P"
                        help="Corte de próximos tokens por probabilidade"
                        initialPosition={modelParams.topP}
                        value={modelParams.topP}
                        max={1}
                        min={0}
                        step={0.001}
                        onChange={(value) => setModelParam("topP", value)}
                      />
                    </PanelRow>
                    <PanelRow>
                      <RangeControl
                        label="Top K"
                        help="Quantidade de próximos tokens selecionados"
                        initialPosition={modelParams.topK}
                        value={modelParams.topK}
                        max={500}
                        min={0}
                        step={1}
                        onChange={(value) => setModelParam("topK", value)}
                      />
                    </PanelRow>
                    <PanelRow>
                      <Button
                        variant="secondary"
                        icon={trash}
                        onClick={() =>
                          setModelParams({ ...options.modelParams })
                        }
                      >
                        Resetar para parâmetros originais
                      </Button>
                    </PanelRow>
                  </PanelBody>
                </Panel>
              </FlexBlock>
            )}
            <FlexBlock className="modal-editor-ai__item">
              <TextareaControl
                label="Instruções para a IA (Prompt)"
                placeholder="Instruções do que a IA deve fazer"
                className="modal-editor-ai__textarea-control"
                value={promptAi}
                onChange={(value) => setPromptAi(value)}
              />
              <TextareaControl
                label="Contexto para a IA"
                placeholder="Informações para contextualizar a tarefa como, título, conteúdo, palavras-chave, etc..."
                className="modal-editor-ai__textarea-control"
                value={contextAi}
                onChange={(value) => setContextAi(value)}
              />
              <NavigableMenu
                orientation="horizontal"
                cycle
                className="modal-editor-ai__menu"
              >
                <Button variant="primary" onClick={sendToAI}>
                  Enviar para a IA
                </Button>
                {/* <Button variant="secondary" onClick={copyContent}>
                  Adicionar conteúdo atual
                </Button> */}
              </NavigableMenu>
            </FlexBlock>
            <FlexBlock className="modal-editor-ai__item">
              <TextareaControl
                label="Respostas da IA"
                placeholder="Deve mostrar os resultados das interações com a IA"
                className="modal-editor-ai__textarea-control"
                value={responseAi}
                onChange={(value) => setResponseAi(value)}
              />
              <NavigableMenu
                orientation="horizontal"
                className="modal-editor-ai__menu"
              >
                <Button variant="primary" onClick={copyResponse}>
                  Copiar
                </Button>
                <Button variant="secondary" onClick={applyAfterContent}>
                  Aplicar ao final
                </Button>
                <Button
                  variant="secondary"
                  onClick={() => setApplyDialog(true)}
                >
                  Aplicar substituindo conteúdo
                </Button>
                <ConfirmDialog
                  isOpen={applyDialog}
                  onConfirm={applyContent}
                  onCancel={() => setApplyDialog(false)}
                >
                  Tem certeza que deseja substituir o conteúdo atual pelo
                  conteúdo gerado para IA?
                </ConfirmDialog>
              </NavigableMenu>
            </FlexBlock>
          </Flex>
          <SnackbarList notices={snacks} />
        </Modal>
      )}
    </>
  );
}

registerPlugin("editor-ai", {
  icon: RobotIcon,
  render: PluginComponent,
});
