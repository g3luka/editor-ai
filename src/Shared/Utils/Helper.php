<?php

namespace EditorAI\Shared\Utils;

use WP_Post;
use WP_Term;

class Helper
{

    public static function getPost(mixed $object = null): WP_Post|false
    {
        if (empty($object)) {
            global $post;
            if ($post instanceof WP_Post) return $post;
        }
        if (is_int($object)) $object = WP_Post::get_instance($object);
        if ($object instanceof WP_Post) return $object;
        return false;
    }

    public static function removeDomain(string $link)
    {
        return preg_replace('/^https?:\/\/[^\/]+(.*)$/', '$1', $link);
    }

    public static function removeDomainIfInternalUrl(string $link)
    {
        /** @TODO Se o link tiver o domínio do site, remove. Caso contrário, retorna como está */
        if (str_contains($link, get_site_url())) return self::removeDomain($link);
        return $link;
    }

    public static function removeProtocol(string $link)
    {
        return preg_replace('/^https?:\/\/(.*)$/', '$1', $link);
    }

    public static function getRelativePermalink(WP_Post | int $post)
    {
        return self::removeDomain(get_permalink($post));
    }

    public static function getRelativeTermLink(WP_Term | int | string $term)
    {
        return self::removeDomain(get_term_link($term));
    }

    public static function toCamelCase(string $input, string $separator = '_'): string
    {
        return lcfirst(str_replace($separator, '', ucwords($input, $separator)));
    }

    public static function getContentHtml(string $permalink)
    {
        $response = wp_remote_get($permalink, [
            'timeout'       => 120,
            'httpversion'   => '1.1',
            'headers'       => [
                'Accept'        => 'text/html',
            ],
        ]);
        if (is_wp_error($response) || !is_array($response) || $response['response']['code'] !== 200) return null;
        return $response['body'];
    }

    public static function getContentHtmlById(int $id)
    {
        $permalink = get_permalink($id);
        if (!$permalink) return null;
        return self::getContentHtml($permalink);
    }

    public static function getContentHtmlToJson(string $permalink)
    {
        $html = self::getContentHtml($permalink);
        return json_encode($html);
    }
    /**
     * Verifica se um usuário tem uma determinada Role/Função
     *
     * @param int $userId
     * @param string $roleName
     * @return bool
     */
    public static function hasUserRole(int $userId, string $roleName): bool
    {
        $user = get_userdata($userId);
        return in_array($roleName, $user->roles);
    }

    public static function isWpJsonRequest()
    {
        if (isset($_SERVER['REQUEST_URI']) && preg_match('/wp-json/', $_SERVER['REQUEST_URI'], $match, PREG_UNMATCHED_AS_NULL)) return true;
        return false;
    }

    public static function preventTwiceHook(string $hookName, int $postId, int $timeoutSeconds = 10, bool $onWpJson = false)
    {
        if (defined('DOING_IMPORT') && DOING_IMPORT) return false;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return true;
        if (wp_is_post_revision($postId)) return true;
        if (wp_is_post_autosave($postId)) return true;
        if ($onWpJson && self::isWpJsonRequest()) return true;
        if (self::isPreventTwice($hookName, $postId, $timeoutSeconds)) return true;
        return false;
    }

    public static function isPreventTwice(string $namespace, int $postId, $timeoutSeconds = 10)
    {
        $namespace = Helper::getClassShortName($namespace);
        $key = "editorai_prevent_twice_{$postId}_{$namespace}";
        if (get_transient($key)) return true;
        set_transient($key, '1', $timeoutSeconds);
        return false;
    }

    public static function getClassShortName(string $class): string
    {
        return basename(str_replace('\\', '/', $class));
    }

    /**
     * Verifica se tem algum tipo de mídia como iframe, audio, vídeo ou imagem no conteúdo
     * @param string $content
     * @return boolean
     */
    public static function hasMedia($content)
    {
        $hasMedia = preg_match('/(<(iframe|video|audio|picture|figure|img|amp-img|amp-video|amp-audio|amp-youtube)[^>]*>)/', $content);
        return ($hasMedia !== FALSE) ? (bool) $hasMedia : false;
    }

    public static function getTags($content, $tag)
    {
        preg_match_all("/<{$tag}[^>]*>(.*)<\/{$tag}>/", $content, $matches);
        return current($matches);
    }

    /**
     * Faz o redirect efetivamente
     *
     * @param string $url
     * @param int $status
     * @return void
     */
    public static function redirectTo(string $url, int $status = 301): void
    {
        header("HTTP/1.1 {$status} Moved Permanently");
        header("Location: {$url}");
        header("Connection: close");
        die;
    }

    public static function removeFirstLastSlashes(string $content)
    {
        return preg_replace("/^\/+|\/+$/", "", $content);
    }

    /**
     * Cria uma data com base em um formato
     *
     * @param mixed $date
     * @param string $format Formato padrão 'd/m/Y'
     * @param string $timezone Padrão 'America/Sao_Paulo'
     * @return DateTime
     */
    public static function getDateTimeObject($date, string $format = 'd/m/Y', string $timezone = 'America/Sao_Paulo')
    {
        return \DateTime::createFromFormat(
            $format,
            $date,
            new \DateTimeZone($timezone)
        );
    }

    /**
     * Converte uma Imagem em data:base64
     *
     * @param string $imageUrl
     * @return string
     */
    public static function imageToBase64(string $imageUrl): string
    {
        $type = pathinfo($imageUrl, PATHINFO_EXTENSION);
        $data = file_get_contents($imageUrl);
        return "data:image/{$type};base64," . base64_encode($data);
    }

    /**
     * Retorna a URL do Thumbnail de um Post em um Blog
     *
     * @param int $blogId
     * @param int $postId
     * @param string $imageSize
     * @return string|null
     */
    public static function getBlogPostThumbnailUrl(int $blogId, int $postId, string $imageSize = 'cardnews_vertical'): ?string
    {
        switch_to_blog($blogId);
        $image = get_the_post_thumbnail_url($postId, $imageSize) ?? null;
        restore_current_blog();
        return $image;
    }

    public static function removeAccents(string $string): string
    {
        $string = trim($string);
        $before = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿ';
        $after  = 'AAAAAAACEEEEIIIIDNOOOOOOUUUUYBSaaaaaaaceeeeiiiidnoooooouuuyyby';
        return strtr($string, $before, $after);
    }
}
