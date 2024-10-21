<?php

namespace Werkraum\Abuseip\Service;

use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AbuseipBackend
{

    public function __construct(
        private FrontendInterface $cache,
        private string $source
    ){
    }

    public function isAbusedIp(string|int $ip): bool
    {
        if (is_string($ip)) {
            $ip = is_numeric($ip) ? (int)$ip : ip2long($ip);
        }

        $ips = $this->getIps();

        return isset($array[$ip]) || array_key_exists($ip, $ips);
    }

    public function getIps(): array
    {
        if (!$this->cache->has('abuseip')) {
            try {
                $this->update();
            } catch (\Throwable) {
                return [];
            }
        }
        return $this->cache->get('abuseip');
    }

    public function update(): void
    {
        $filePath = $this->getFilePath();
        if (!file_exists($filePath)) {
            $this->updateFromRemoteSource();
        }

        $ips = file_get_contents($filePath);
        $ipsAsLong = json_decode($ips, true, 512, JSON_THROW_ON_ERROR);

        // save values as keys to have a fast lookup via keys
        $this->cache->set('abuseip', array_flip($ipsAsLong));
    }

    public function updateFromRemoteSource(): void
    {
        $content = GeneralUtility::getUrl($this->source);
        if ($content !== false) {
            $ips = $this->parseContent($content);

            $ipsAsLong = array_map(static function(string $ip) {
                return ip2long($ip);
            }, $ips);

            $filePath = $this->getFilePath();
            file_put_contents($filePath, json_encode($ipsAsLong, JSON_THROW_ON_ERROR));
            GeneralUtility::fixPermissions($filePath);
        }
    }

    private function getFilePath(): string
    {
        // check if local file exists
        $path = Environment::getPublicPath() . '/typo3temp/assets/abuseip/';
        if (!is_dir($path)) {
            GeneralUtility::mkdir_deep($path);
        }
        $filePath = $path . "abuseip.json";
        return $filePath;
    }

    private function parseContent(string $content): array
    {
        $lines = explode("\n", $content);

        // Remove inline comments and validate that every line contains a valid IP address
        $ips = array_filter(
            array_map(fn($line) => preg_replace('/\s*#.*$/', '', trim($line)), $lines),
            fn($line) => filter_var($line, FILTER_VALIDATE_IP) !== false
        );

        return array_values(array_unique($ips));
    }

}