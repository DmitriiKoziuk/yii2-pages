<?php declare(strict_types=1);

namespace DmitriiKoziuk\yii2Pages\services;

use yii\db\Connection;
use DmitriiKoziuk\yii2Base\helpers\UrlHelper;
use DmitriiKoziuk\yii2Base\services\DBActionService;
use DmitriiKoziuk\yii2Base\traits\ModelValidatorTrait;
use DmitriiKoziuk\yii2Base\exceptions\DataNotValidException;
use DmitriiKoziuk\yii2Base\exceptions\ExternalComponentException;
use DmitriiKoziuk\yii2UrlIndex\forms\UrlCreateForm;
use DmitriiKoziuk\yii2UrlIndex\forms\UrlUpdateForm;
use DmitriiKoziuk\yii2UrlIndex\interfaces\UrlIndexServiceInterface;
use DmitriiKoziuk\yii2Pages\PagesModule;
use DmitriiKoziuk\yii2Pages\exceptions\PageCreateFormNotValid;
use DmitriiKoziuk\yii2Pages\interfaces\PageRepositoryInterface;
use DmitriiKoziuk\yii2Pages\interfaces\PageServiceInterface;
use DmitriiKoziuk\yii2Pages\entities\PageEntity;
use DmitriiKoziuk\yii2Pages\forms\PageCreateForm;
use DmitriiKoziuk\yii2Pages\forms\PageUpdateForm;

class PageService extends DBActionService implements PageServiceInterface
{
    use ModelValidatorTrait;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var UrlIndexServiceInterface
     */
    private $urlIndexService;

    public function __construct(
        PageRepositoryInterface $pageRepository,
        UrlIndexServiceInterface $urlIndexService,
        Connection $db = null
    ) {
        parent::__construct($db);
        $this->pageRepository = $pageRepository;
        $this->urlIndexService = $urlIndexService;
    }

    /**
     * @param PageCreateForm $pageCreateForm
     * @return PageUpdateForm
     * @throws DataNotValidException
     * @throws ExternalComponentException
     * @throws \Exception
     */
    public function createPage(PageCreateForm $pageCreateForm): PageUpdateForm
    {
        $this->validateModels(
            [$pageCreateForm],
            new PageCreateFormNotValid('Page create form not valid.')
        );

        try {
            $this->beginTransaction();
            $pageEntity = $this->savePage($pageCreateForm);
            $this->addPageUrlToIndex($pageEntity->name, $pageEntity->id);
            $this->commitTransaction();
            return new PageUpdateForm($pageEntity->getAttributes());
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    private function savePage(PageCreateForm $pageCreateForm): PageEntity
    {
        $pageEntity = new PageEntity($pageCreateForm->getAttributes());
        $this->pageRepository->save($pageEntity);
        return $pageEntity;
    }

    private function addPageUrlToIndex(string $pageName, int $pageId): UrlUpdateForm
    {
        $urlCreateForm = new UrlCreateForm();
        $urlCreateForm->url = '/' . UrlHelper::getSlugFromString($pageName);
        $urlCreateForm->module_name = PagesModule::ID;
        $urlCreateForm->controller_name = PagesModule::FRONTEND_CONTROLLER_NAME;
        $urlCreateForm->action_name = PagesModule::FRONTEND_CONTROLLER_ACTION;
        $urlCreateForm->entity_id = (string) $pageId;
        return $this->urlIndexService->addUrl($urlCreateForm);
    }
}