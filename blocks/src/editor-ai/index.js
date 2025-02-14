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
import {
  box,
  title,
  plus,
  pencil,
  settings,
  trash,
  addCard,
  atSymbol,
} from "@wordpress/icons";
import { useState } from "@wordpress/element";

const options = {
  endpointApi: "/editor-ai/v1/playground",
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
  const [snacks, setSnacks] = useState([]);
  const [applyDialog, setApplyDialog] = useState(false);
  const [promptAi, setPromptAi] = useState("");
  const [contextAi, setContextAi] = useState("");
  const [responseAi, setResponseAi] = useState("");
  const { removeBlocks, insertBlocks } = useDispatch("core/block-editor");
  const userCan = useSelect((select) =>
    select("core").canUser("create", "users"),
  );

  const useCases = {
    rewriteContent: {
      buttonLabel: "Reescrever conteúdo",
      prompt: `Responda como um jornalista profissional que trabalha em um site popular de notícias locais voltado ao público da cidade de Curitiba, no estado do Paraná. Sua tarefa é reescrever o Conteúdo de contexto fornecido abaixo, aplicando técnicas de jornalismo local e práticas recomendadas nos melhores manuais de redação jornalística.\n\nAs diretrizes para a reescrita são:\n* O texto deve ser reescrito de forma única, utilizando linguagem neutra, clara e acessível, com um tom moderno e dinâmico, típico de sites digitais de notícias locais. Adicione elementos sutis de informalidade sem comprometer a seriedade.\n* Identifique e utilize a palavra-chave mais relevante do conteúdo para trabalhar estratégias de SEO. Insira a palavra-chave de forma estratégica em títulos, subtítulos e corpo do texto, sem comprometer a legibilidade ou fluidez do conteúdo.\n* Otimize a qualidade do texto por meio de revisões cuidadosas: elimine redundâncias, melhore a coesão entre os parágrafos e garanta a precisão das informações, mas use apenas as informações e dados que estão presentes no texto original.\n* Certifique-se de que o texto reescrito seja mais direto, envolvente e impactante do que o original.\n* Caso o texto original inclua listas, intertítulos ou subtítulos, mantenha a mesma estrutura no texto que você reescrever. Reorganize as informações, se necessário, para melhorar a leitura e destacar os pontos mais relevantes.\n* Respeite o tamanho original do texto fornecido e mantenha no mínimo 1.000 caracteres. Evite adições ou cortes significativos de informações importantes, mas priorize a clareza e o impacto do conteúdo.\n* Caso o texto original tenha palavras ou frases entre aspas, preserve sem qualquer alteração.\n* Não crie citações; copie integralmente e sem alterações apenas as citações existentes no texto original.\n* Não crie frase de conclusão ou fechamento ao final do texto.\n\nAgora reescreva a notícia abaixo de acordo com diretrizes informadas:`,
      open: () => {
        setPromptAi(useCases.rewriteContent.prompt);
        copyContent();
        setOpen(true);
      },
    },
    generateNewContent: {
      buttonLabel: "Gerar um novo conteúdo",
      prompt:
        "- Atue como um jornalista profissional que trabalha em um site popular de notícias locais\n- Gere uma matéria jornalística, usando as informações de contexto, com informações relevantes para informar os leitores, usando as melhores técnicas de jornalismo local\n- Use as práticas recomendadas nos melhores manuais de redação para jornalismo\n- Procure identificar a palavra chave mais importante e trabalhe conceito de SEO no conteúdo\n- Use linguagem leve, sutilmente próxima ao leitor, explicando siglas e conceitos\n- A notícia gerada deverá ter entre 4 e 8 parágrafos, dependendo da complexidade do tema e necessidade de explicar o contexto.",
      context: `Qual é o tema ou assunto da matéria?\n\n\nQual é o público-alvo da matéria?\n\n\nPalavras-chave de destaque?\n\n\nTem links de referência?\n`,
      open: () => {
        setPromptAi(useCases.generateNewContent.prompt);
        setContextAi(useCases.generateNewContent.context);
        setOpen(true);
      },
    },
    generateFollowup: {
      buttonLabel: "Ideias de desdobramentos (suite)",
      prompt: `"Suite", designa a reportagem que explora os desdobramentos de um fato que foi notícia, retomar um assunto, perscrutar seus desdobramentos, a fim de transformá-lo outra vez em notícia.\n- Atue como um jornalista profissional que trabalha em um site popular de notícias locais\n- Seu objetivo é criar 5 ideias de desdobramentos (Suite) para a matéria jornalística que está no contexto, para que que sejam usadas como sugestões de novas pautas.\n- Use as práticas recomendadas nos melhores manuais de redação para jornalismo\n- Você deve explorar os desdobramentos de um fato que foi notícia, retomar um assunto, perscrutar seus desdobramentos, a fim de transformá-lo outra vez em notícia.\n- Ao menos uma das ideias deve ser pensada no público local`,
      open: () => {
        setPromptAi(useCases.generateFollowup.prompt);
        copyContent();
        setOpen(true);
      },
    },
    generateExpansion: {
      buttonLabel: "Ideias de expansão",
      prompt: `- Atue como um jornalista profissional, especialista em criar novas pautas, que trabalha em um site popular de notícias locais\n- Seu objetivo é criar 8 ideias de expansão a partir do contexto com perguntas provocativas e reflexivas, que ajudem outros jornalistas a ter insights e escrever novas pautas. \n- Procure ampliar os temas, gerando novos insights por proximidade, associação, comparação, confronto ou similaridade \n- Use as práticas recomendadas nos melhores manuais de redação para jornalismo\n- Use a criatividade para perguntar outros temas além dos que são tratados no contexto\n- Tente gerar insights sobre aplicações, hipóteses, comparações, consequências, benefícios, impactos, repercussões\n- Ao menos uma das perguntas deve ser pensada no público local\n- suas perguntas podem envolver os fatos, entidades, personagens, entre outros\n- Você pode usar aleatoriamente em suas perguntas termos como "quais", "de que maneira", "quando", "onde", "quem", "por que", "o que", "como", "que", "quanto", entre outras`,
      open: () => {
        setPromptAi(useCases.generateExpansion.prompt);
        copyContent();
        setOpen(true);
      },
    },
    generatePostsFacebook: {
      buttonLabel: "Ideias de posts para o Facebook",
      prompt: `- ATUE COMO UM PROFISSIONAL SOCIAL MEDIA que trabalha em um site de notícias locais e administra uma página no Facebook\n- Crie 5 ideias para posts no Facebook usando as melhores técnicas de social media para engajar o usuário; e 5 textos para copy + link\n- use as informações do contexto\n- use as melhores técnicas de copywriting e engajamento para redes sociais.`,
      open: () => {
        setPromptAi(useCases.generatePostsFacebook.prompt);
        copyContent();
        setOpen(true);
      },
    },
    generatePostsInstagram: {
      buttonLabel: "Ideias de posts para o Instagram",
      prompt: `- ATUE COMO UM PROFISSIONAL SOCIAL MEDIA que trabalha em um site de notícias locais e administra um perfil no Instagram\n- Crie 7 sugestões de ideias para post no feed, stories ou reel no Instagram usando as melhores técnicas de social media para engajar o usuário\n- Use as informações do contexto\n- Use as melhores técnicas de copywrititing e engajamento para Instagram`,
      open: () => {
        setPromptAi(useCases.generatePostsInstagram.prompt);
        copyContent();
        setOpen(true);
      },
    },
    generateTitles: {
      buttonLabel: "Ideias de título do Post",
      prompt:
        "- Atue como um jornalista profissional que trabalha em um site popular de notícias locais, que é especialista em escrever títulos.\n- Seu objetivo é criar 7 títulos para o conteúdo do contexto.\n- Use as práticas recomendadas nos melhores manuais de redação para jornalismo\n- crie títulos únicos, Seja específico, transmitir um senso de urgência, Seu título tem que ser útil\n- Dê a informação mais importante e seja claro\n- Coloque ao menos um verbo nos títulos\n- Priorize as palavras-chave para SEO\n- Se possível, use números\n- Se possível, gere curiosidade\n- Deixe claro que você tem uma explicação\n- Crie senso de urgência\n- Crie uma familiaridade com o leitor\n- Ao menos um dos títulos deve ser uma pergunta\n- Trabalhar com termos contrastantes para criar uma polêmica\n- Se houver, você pode citar referências",
      open: () => {
        setPromptAi(useCases.generateTitles.prompt);
        copyContent();
        setOpen(true);
      },
    },
    generateHeadlinesHomePage: {
      buttonLabel: "Ideias de título para a Capa",
      prompt:
        "- Atue como um jornalista profissional que trabalha em um site popular de notícias locais, que é especialista em escrever títulos.\n- Seu objetivo é criar 7 títulos criativos para A HOME DO SITE sobre o conteúdo do contexto com o objetivo de chamar a atenção do usuário e faze-lo clicar na notícia.\n- Use as práticas recomendadas nos melhores manuais de redação para jornalismo\n- crie títulos únicos, Seja específico, transmitir um senso de urgência, Seu título tem que ser útil\n- Dê a informação mais importante e seja claro\n- Coloque ao menos um verbo nos títulos\n- Priorize as palavras-chave para SEO\n- Se possível, use números\n- Gere curiosidade\n- você pode usar termo de comparação: como “melhores”, “piores”, “mais”, “menos”, “maiores”, entre outros.\n- Deixe claro que você tem uma explicação\n- Crie senso de urgência\n- Crie uma familiaridade com o leitor\n- Ao menos um dos títulos deve ser uma pergunta\n- Trabalhar com termos contrastantes para criar uma polêmica\n- Se houver, você pode citar referências",
      open: () => {
        setPromptAi(useCases.generateHeadlinesHomePage.prompt);
        copyContent();
        setOpen(true);
      },
    },
    generateHeadlinesDiscover: {
      buttonLabel: "Ideias de título para o Discover",
      prompt:
        "- Atue como um jornalista profissional que trabalha em um site popular de notícias locais, que é especialista em escrever títulos de até 66 caracteres.\n- Seu objetivo é criar 7ideias de título que performem bem na ferramenta de recomendação de conteúdo Google Discover utilizando técnicas de SEO, dentro das diretrizes de qualidade de conteúdo do google, para de chamar a atenção do usuário e faze-lo clicar na notícia.\n- Os títulos deverão ter no máximo 66 caracteres\n- Leve em consideração as diretrizes contidas nestes links\nhttps://developers.google.com/search/docs/appearance/google-discover?hl=pt-br\nhttps://support.google.com/websearch/answer/9982767?hl=pt-br\nhttps://developers.google.com/search/docs/fundamentals/creating-helpful-content?hl=pt-br",
      open: () => {
        setPromptAi(useCases.generateHeadlinesDiscover.prompt);
        setContextAi(useCases.generateHeadlinesDiscover.context);
        copyContent();
        setOpen(true);
      },
    },
  };

  const closeModal = () => setOpen(false);
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
      path: options.endpointApi,
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
          <p>
            Deixe que ela te dê insights relevantes para você focar no que mais
            importa!
          </p>
          <Flex direction="column">
            <Button
              variant="secondary"
              icon={pencil}
              onClick={useCases.rewriteContent.open}
            >
              {useCases.rewriteContent.buttonLabel}
            </Button>
            <Button
              variant="secondary"
              icon={addCard}
              onClick={useCases.generateNewContent.open}
            >
              {useCases.generateNewContent.buttonLabel}
            </Button>
            <DropdownMenu
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
            />
            <DropdownMenu
              text="Gerar títulos"
              label="Gerar ideias de títulos"
              icon={title}
              controls={[
                {
                  title: useCases.generateTitles.buttonLabel,
                  onClick: useCases.generateTitles.open,
                },
                {
                  title: useCases.generateHeadlinesHomePage.buttonLabel,
                  onClick: useCases.generateHeadlinesHomePage.open,
                },
                {
                  title: useCases.generateHeadlinesDiscover.buttonLabel,
                  onClick: useCases.generateHeadlinesDiscover.open,
                },
              ]}
            />
            <DropdownMenu
              text="Redes Sociais"
              label="Gerar conteúdos para redes sociais"
              icon={atSymbol}
              controls={[
                {
                  title: useCases.generatePostsFacebook.buttonLabel,
                  onClick: useCases.generatePostsFacebook.open,
                },
                {
                  title: useCases.generatePostsInstagram.buttonLabel,
                  onClick: useCases.generatePostsInstagram.open,
                },
              ]}
            />
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
