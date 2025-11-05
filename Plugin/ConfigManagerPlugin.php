<?php declare(strict_types=1);

namespace HenriqueKieckbusch\AutoCSP\Plugin;

use Magento\Csp\Api\Data\ModeConfiguredInterface;
use Magento\Csp\Model\Mode\ConfigManager;
use Magento\Csp\Model\Mode\Data\ModeConfigured;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;

class ConfigManagerPlugin
{
    private const XML_PATH_CAPTURE = 'henrique_kieckbusch/autocsp/capture';
    private const XML_PATH_MODULE_ENABLED = 'henrique_kieckbusch/autocsp/enabled';
    private const XML_PATH_OVERRIDE_MODE = 'henrique_kieckbusch/autocsp/override_mode';
    private const XML_PATH_ENFORCED_MODE = 'henrique_kieckbusch/autocsp/enforced_mode';

    /**
     * Overriden result if this module is enabled and override settings are configured
     *
     * @var ModeConfiguredInterface
     */
    private readonly ModeConfiguredInterface $modeConfigured;

    // phpcs:ignore
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly UrlInterface $urlBuilder,
    ) {
    }

    /**
     * Update the reportOnly and reportUri properties based on configuration
     *
     * Priority 1: If capture mode is enabled, always set report-only and report-uri
     * Priority 2: If override mode is enabled, set reportOnly based on enforced mode
     * Priority 3: Respect Magento's native CSP configuration
     *
     * @param ConfigManager $subject
     * @param ModeConfiguredInterface $result
     * @return ModeConfiguredInterface
     */
    public function afterGetConfigured(
        ConfigManager $subject,
        ModeConfiguredInterface $result
    ) : ModeConfiguredInterface {
        if (isset($this->modeConfigured)) {
            return $this->modeConfigured;
        }

        $isAutoCspEnabled = $this->scopeConfig->isSetFlag(self::XML_PATH_MODULE_ENABLED);

        // If module is disabled, respect Magento's native config
        if (!$isAutoCspEnabled) {
            $this->modeConfigured = $result;
            return $this->modeConfigured;
        }

        $isCaptureModeEnabled = $this->scopeConfig->isSetFlag(self::XML_PATH_CAPTURE);
        $isOverrideModeEnabled = $this->scopeConfig->isSetFlag(self::XML_PATH_OVERRIDE_MODE);
        $isEnforcedModeEnabled = $this->scopeConfig->isSetFlag(self::XML_PATH_ENFORCED_MODE);

        // Priority 1: If capture mode is enabled, always set report-only and report-uri
        if ($isCaptureModeEnabled) {
            $this->modeConfigured = new ModeConfigured(
                true, // Always report-only when capturing
                $this->urlBuilder->getUrl('autocsp/report/index'),
            );
        }
        // Priority 2: If override mode is enabled, set reportOnly based on enforced mode
        elseif ($isOverrideModeEnabled) {
            $this->modeConfigured = new ModeConfigured(
                !$isEnforcedModeEnabled, // If enforced, reportOnly = false
                $result->getReportUri(), // Keep existing report URI
            );
        }
        // Priority 3: Respect Magento's native CSP configuration
        else {
            $this->modeConfigured = $result;
        }

        return $this->modeConfigured;
    }
}
