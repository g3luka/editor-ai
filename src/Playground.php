<?php

namespace EditorAI;

use EditorAI\Shared\Contracts\Exportable;
use EditorAI\Shared\Clients\Http\HttpClient;
use EditorAI\Shared\Clients\Http\HttpPayload;
use EditorAI\Shared\Traits\UseConfig;
use WP_REST_Request;

class Playground implements Exportable
{
    use UseConfig;

    public array $input;
    public string $taskName;

    const MODELS = [
        ["name" => "AMAZON_NOVA_PRO", "label" => "Amazon Nova Pro"],
        ["name" => "AMAZON_NOVA_LITE", "label" => "Amazon Nova Lite"],
        ["name" => "AMAZON_NOVA_MICRO", "label" => "Amazon Nova Micro"],
        ["name" => "TITAN_TEXT_G1_LITE", "label" => "Amazon Titan Text G1 Lite"],
        ["name" => "TITAN_TEXT_G1_EXPRESS", "label" => "Amazon Titan Text G1 Express"],
        ["name" => "CLAUDE_3_5_SONNET", "label" => "Anthropic Claude 3.5 Sonnet"],
        ["name" => "CLAUDE_3_SONNET", "label" => "Anthropic Claude 3 Sonnet"],
        ["name" => "CLAUDE_3_HAIKU", "label" => "Anthropic Claude 3 Haiku"],
        ["name" => "CLAUDE_2_1", "label" => "Anthropic Claude 2.1"],
        ["name" => "CLAUDE_2", "label" => "Anthropic Claude 2"],
        ["name" => "CLAUDE_INSTANT", "label" => "Anthropic Claude Instant"],
        ["name" => "COREHE_COMMAND_R_PLUS", "label" => "Corehe Command R Plus"],
        ["name" => "COREHE_COMMAND_R", "label" => "Corehe Command R"],
        ["name" => "JURASSIC2_ULTRA", "label" => "Jurassic2 Ultra"],
        ["name" => "JURASSIC2_MID", "label" => "Jurassic2 Mid"],
        ["name" => "META_LLAMA_3_70B_INSTRUCT", "label" => "Meta Llama 3 70b Instruct"],
        ["name" => "META_LLAMA_3_8B_INSTRUCT", "label" => "Meta Llama 3 8b Instruct"],
        ["name" => "META_LLAMA_2_CHAT_70B", "label" => "Meta Llama 2 Chat 70b"],
        ["name" => "META_LLAMA_2_CHAT_13B", "label" => "Meta Llama 2 Chat 13b"],
        ["name" => "MIXTRAL_LARGE", "label" => "Mixtral Large"],
        ["name" => "MIXTRAL_8X7B", "label" => "Mixtral 8x7b"],
        ["name" => "MISTRAL_7B", "label" => "Mistral 7b"],
    ];

    public function __construct(array $payload)
    {
        $this->taskName = $payload['task'];
        $this->input = $payload['input'];
    }

    public function export()
    {
        return $this->parse();
    }

    private function parse(): array
    {
        $context = trim(strip_tags($this->input['context']));
        $stopSentence = array_map('trim', explode(',', $this->getOption('editorai_default_stopsentence') ?? ''));
        $result = [
            'site'              => $this->getOption('domain_id'),
            "task"              => $this->taskName,
            "input"             => [
                "model"             => $this->getOption('editorai_default_model'),
                "max_tokens"        => (int) $this->getOption('editorai_default_maxtokens'),
                "temperature"       => (float) $this->getOption('editorai_default_temperature'),
                "top_k"             => (int) $this->getOption('editorai_default_topp'),
                "top_p"             => (float) $this->getOption('editorai_default_topk'),
                "stop"              => $stopSentence,
                ...$this->input,
                "context"           => $context,
            ],
        ];

        if (empty($result['input']['stop'])) unset($result['input']['stop']);

        return $result;
    }

    public static function restCallback(WP_REST_Request $request)
    {
        $payload = $request->get_json_params();
        return self::request($payload);
    }

    public static function request($payload)
    {
        $body = new Playground($payload);
        $endpoint = get_option('editorai_athena_endpoint');
        $payload = new HttpPayload();
        $payload
            ->setEndpoint($endpoint)
            ->setBody($body->export());
        $client = new HttpClient();
        return $client->send($payload->export());
    }
}
