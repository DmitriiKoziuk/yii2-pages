<?php
namespace DmitriiKoziuk\yii2Pages;

use Yii;
use yii\di\Container;
use yii\web\Application as WebApp;
use yii\base\Application as BaseApp;
use DmitriiKoziuk\yii2ModuleManager\interfaces\ModuleInterface;
use DmitriiKoziuk\yii2Base\helpers\UrlHelper;
use DmitriiKoziuk\yii2Base\helpers\FileHelper;
use DmitriiKoziuk\yii2CustomUrls\services\UrlService;
use DmitriiKoziuk\yii2Pages\repositories\PageRepository;
use DmitriiKoziuk\yii2Pages\services\PageService;

class PagesModule extends \yii\base\Module implements ModuleInterface
{
    const ID = 'dk-pages';

    const TRANSLATE = self::ID;

    const CONTENT_STORAGE_PATH = '@frontend/storage/dk-pages/content';

    const FRONTEND_CONTROLLER_NAME = 'page';

    const FRONTEND_CONTROLLER_ACTION = 'index';

    /**
     * @var Container
     */
    public $diContainer;

    /**
     * Overwrite this param if you backend app id is different from default.
     * @var string
     */
    public $backendAppId;

    /**
     * Overwrite this param if you backend app id is different from default.
     * @var string
     */
    public $frontendAppId;

    public function init(): void
    {
        /** @var BaseApp $app */
        $app = $this->module;
        $this->_initLocalProperties($app);
        $this->_registerTranslation($app);
        $this->_registerClassesToDIContainer($app);
    }

    public function getId(): string
    {
        return self::ID;
    }

    public function getBackendMenuItems(): array
    {
        return ['label' => 'Pages', 'url' => ['/' . self::ID . '/page/index']];
    }

    /**
     * @param BaseApp $app
     * @throws \InvalidArgumentException
     */
    private function _initLocalProperties(BaseApp $app): void
    {
        if (empty($this->backendAppId)) {
            throw new \InvalidArgumentException('Property backendAppId not set.');
        }
        if (empty($this->frontendAppId)) {
            throw new \InvalidArgumentException('Property frontendAppId not set.');
        }
        if ($app instanceof WebApp && $app->id == $this->backendAppId) {
            $this->controllerNamespace = __NAMESPACE__ . '\controllers\backend';
            $this->viewPath = '@DmitriiKoziuk/yii2Pages/views/backend';
        }
        if ($app instanceof WebApp && $app->id == $this->frontendAppId) {
            $this->controllerNamespace = __NAMESPACE__ . '\controllers\frontend';
            $this->viewPath = '@DmitriiKoziuk/yii2Pages/views/frontend';
        }
    }

    private function _registerTranslation(BaseApp $app): void
    {
        $app->i18n->translations[self::TRANSLATE] = [
            'class'          => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en',
            'basePath'       => '@DmitriiKoziuk/yii2Pages/messages',
        ];
    }

    private function _registerClassesToDIContainer(BaseApp $app): void
    {
        $this->diContainer->setSingleton(PageRepository::class);
        $this->diContainer->setSingleton(PageService::class, function () use ($app) {
            /** @var PageRepository $pageRepository */
            $pageRepository = $this->diContainer->get(PageRepository::class);
            /** @var UrlHelper $urlHelper */
            $urlHelper = $this->diContainer->get(UrlHelper::class);
            /** @var FileHelper $fileHelper */
            $fileHelper = $this->diContainer->get(FileHelper::class);
            /** @var UrlService $urlService */
            $urlService = $this->diContainer->get(UrlService::class);
            return new PageService(
                Yii::getAlias(self::CONTENT_STORAGE_PATH),
                $pageRepository,
                $urlHelper,
                $fileHelper,
                $urlService,
                $app->db
            );
        });
    }
}