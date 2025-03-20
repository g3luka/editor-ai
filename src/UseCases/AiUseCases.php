<?php

namespace EditorAI\UseCases;

use WP_REST_Request;

class AiUseCases
{
    public static function restCallback(WP_REST_Request $request)
    {
        return [
            [
                "buttonLabel" => "Gerar um novo conteúdo",
                "buttonVariant" => "primary",
                "buttonIcon" => "addCard",
                "prompt" => "- Atue como um redator de conteúdo escrevendo para um site da área de medicina\n- Gere uma matéria, com as informações de contexto, usando as melhores técnicas de redação e copywriting\n- Procure identificar a palavra chave mais importante e trabalhe conceitos de SEO no conteúdo\n- Use linguagem formal e profissional do público médico\n- Explique siglas e conceitos\n- A notícia gerada deverá ter entre 8 e 16 parágrafos, dependendo da complexidade do tema e necessidade de explicar o contexto\n- Escreva a matéria sempre em Português do Brasil\n- Use padrões de formatação que incluem lista com marcadores e subtítulos\n- Inserir uma meta descrição no final do texto com até 155 caracteres e com o subtítulo Meta Description",
                "context" => "Qual é o tema ou assunto da matéria?\n\n\nQual é o público-alvo da matéria?\nMédicos, Profissionais da área de saúde e Estudantes de medicina\n\nPalavras-chave de destaque?\n\n\nTem links de referência?\n",
            ],
            [
                "buttonLabel" => "Reescrever conteúdo",
                "prompt" => "Responda como um jornalista profissional que trabalha em um site popular de notícias locais voltado ao público da cidade de Curitiba, no estado do Paraná. Sua tarefa é reescrever o Conteúdo de contexto fornecido abaixo, aplicando técnicas de jornalismo local e práticas recomendadas nos melhores manuais de redação jornalística.\n\nAs diretrizes para a reescrita são:\n* O texto deve ser reescrito de forma única, utilizando linguagem neutra, clara e acessível, com um tom moderno e dinâmico, típico de sites digitais de notícias locais. Adicione elementos sutis de informalidade sem comprometer a seriedade.\n* Identifique e utilize a palavra-chave mais relevante do conteúdo para trabalhar estratégias de SEO. Insira a palavra-chave de forma estratégica em títulos, subtítulos e corpo do texto, sem comprometer a legibilidade ou fluidez do conteúdo.\n* Otimize a qualidade do texto por meio de revisões cuidadosas: elimine redundâncias, melhore a coesão entre os parágrafos e garanta a precisão das informações, mas use apenas as informações e dados que estão presentes no texto original.\n* Certifique-se de que o texto reescrito seja mais direto, envolvente e impactante do que o original.\n* Caso o texto original inclua listas, intertítulos ou subtítulos, mantenha a mesma estrutura no texto que você reescrever. Reorganize as informações, se necessário, para melhorar a leitura e destacar os pontos mais relevantes.\n* Respeite o tamanho original do texto fornecido e mantenha no mínimo 1.000 caracteres. Evite adições ou cortes significativos de informações importantes, mas priorize a clareza e o impacto do conteúdo.\n* Caso o texto original tenha palavras ou frases entre aspas, preserve sem qualquer alteração.\n* Não crie citações; copie integralmente e sem alterações apenas as citações existentes no texto original.\n* Não crie frase de conclusão ou fechamento ao final do texto.\n\nAgora reescreva a notícia abaixo de acordo com diretrizes informadas:",
                "contextContent" => true,
            ],
            [
                "buttonLabel" => "Ideias de título do Post",
                "buttonIcon" => "title",
                "prompt" => "- Atue como um jornalista profissional que trabalha em um site popular de notícias locais, que é especialista em escrever títulos.\n- Seu objetivo é criar 7 títulos para o conteúdo do contexto.\n- Use as práticas recomendadas nos melhores manuais de redação para jornalismo\n- crie títulos únicos, Seja específico, transmitir um senso de urgência, Seu título tem que ser útil\n- Dê a informação mais importante e seja claro\n- Coloque ao menos um verbo nos títulos\n- Priorize as palavras-chave para SEO\n- Se possível, use números\n- Se possível, gere curiosidade\n- Deixe claro que você tem uma explicação\n- Crie senso de urgência\n- Crie uma familiaridade com o leitor\n- Ao menos um dos títulos deve ser uma pergunta\n- Trabalhar com termos contrastantes para criar uma polêmica\n- Se houver, você pode citar referências",
                "contextContent" => true
            ],
        ];
    }
}
