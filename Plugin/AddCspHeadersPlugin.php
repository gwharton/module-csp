<?php

namespace Gw\Csp\Plugin;

use Magento\Csp\Api\Data\PolicyInterface;
use Magento\Csp\Api\ModeConfigManagerInterface;
use Magento\Csp\Model\Policy\Renderer\SimplePolicyHeaderRenderer;
use Magento\Framework\App\Response\HttpInterface as HttpResponse;

class AddCspHeadersPlugin
{
    private $modeConfig;

    /**
     * @param ModeConfigManagerInterface $modeConfig
     */
    public function __construct(ModeConfigManagerInterface $modeConfig)
    {
        $this->modeConfig = $modeConfig;
    }

    /**
     * @param SimplePolicyHeaderRenderer $subject
     * @param PolicyInterface $policy
     * @param HttpResponse $response
     * @return array
     */
    public function beforeRender(SimplePolicyHeaderRenderer $subject, PolicyInterface $policy, HttpResponse $response): array
    {
        $response->setHeader(
            'Report-To',
            '{"group":"default","max_age":10886400,"endpoints":[{"url":"https://www.lubefinder.com/reporting/"}],"include_subdomains":true}'
        );
        $response->setHeader(
            'Reporting-Endpoints',
            'default="https://www.lubefinder.com/reporting/"'
        );
        $response->setHeader(
            'NEL',
            '{"report_to":"default","max_age":2592000,"include_subdomains":true,"failure_fraction":1.0}'
        );
        $response->setHeader(
            'Cross-Origin-Embedder-Policy-Report-Only',
            'require-corp; report-to="default"'
        );
        $response->setHeader(
            'Cross-Origin-Opener-Policy-Report-Only',
            'same-origin; report-to="default"'
        );
        return [$policy, $response];
    }

    /**
     * @param SimplePolicyHeaderRenderer $subject
     * @param null $result
     * @param PolicyInterface $policy
     * @param HttpResponse $response
     * @return void
     */
    public function afterRender(SimplePolicyHeaderRenderer $subject, $result, PolicyInterface $policy, HttpResponse $response): void
    {
        $config = $this->modeConfig->getConfigured();
        if ($config->isReportOnly()) {
            $header = 'Content-Security-Policy-Report-Only';
        } else {
            $header = 'Content-Security-Policy';
        }
        $existing = $response->getHeader($header);
        $response->setHeader($header, $existing->getFieldValue() . " report-to default;", true);
    }
}
