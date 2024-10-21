<?php

namespace Werkraum\Abuseip\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use Werkraum\Abuseip\Service\AbuseipBackend;

class Abuseip implements MiddlewareInterface
{

    public function __construct(
        private AbuseipBackend $backend,
        private ExtensionConfiguration $extensionConfiguration
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $remoteAddress = $request->getServerParams()['REMOTE_ADDR'];

        $whitelist = $this->extensionConfiguration->get('abuseip', 'whitelistIps');
        $whitelist = $this->parseIpList($whitelist);

        if (in_array($remoteAddress, $whitelist, true)) {
            return $handler->handle($request);
        }

        $blacklist = $this->extensionConfiguration->get('abuseip', 'blacklistIps');
        $blacklist = $this->parseIpList($blacklist);

        if ($this->backend->isAbusedIp($remoteAddress) || in_array($remoteAddress, $blacklist)) {
            return GeneralUtility::makeInstance(ErrorController::class)
                ->accessDeniedAction($request, 'Your IP address has been blocked');
        }

        return $handler->handle($request);
    }

    private function parseIpList(array|string $list): array
    {
        $list = is_array($list) ? $list : explode(',', $list);
        $list = array_filter(
            array_map('trim', $list),
            static fn($ip) => filter_var($ip, FILTER_VALIDATE_IP) !== false)
        ;
        return $list;
    }

}