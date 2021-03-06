<?php

namespace App\Twig\Modules\Notes;

use App\Controller\Modules\Notes\MyNotesCategoriesController;
use App\Controller\Modules\Notes\MyNotesController;
use App\Controller\Core\Application;
use App\Services\Exceptions\ExceptionDuplicatedTranslationKey;
use Doctrine\DBAL\DBALException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MyNotes extends AbstractExtension {

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var MyNotesController $my_notes_controller
     */
    private $my_notes_controller;

    /**
     * @var MyNotesCategoriesController $my_notes_categories_controller
     */
    private $my_notes_categories_controller;

    public function __construct(Application $app, MyNotesController $my_notes_controller, MyNotesCategoriesController $my_notes_categories_controller) {
        $this->app = $app;
        $this->my_notes_controller = $my_notes_controller;
        $this->my_notes_categories_controller = $my_notes_categories_controller;
    }

    public function getFunctions() {
        return [
            new TwigFunction('getAccessibleNotesCategories',    [$this, 'getAccessibleNotesCategories']),
            new TwigFunction('hasCategoryFamilyVisibleNotes',   [$this, 'hasCategoryFamilyVisibleNotes']),
            new TwigFunction('isNotesCategoryActive',           [$this, 'isNotesCategoryActive']),
            new TwigFunction('buildNotesCategoriesDepths',      [$this, 'buildNotesCategoriesDepths']),
            new TwigFunction('getAllNotesCategories',      [$this, 'getAllNotesCategories']),
        ];
    }

    /**
     * @param string $category_id
     * @return bool
     * @throws ExceptionDuplicatedTranslationKey
     */
    public function hasCategoryFamilyVisibleNotes(string $category_id) {
        $hasCategoryFamilyVisibleNotes = $this->my_notes_controller->hasCategoryFamilyVisibleNotes($category_id);
        return $hasCategoryFamilyVisibleNotes;
    }

    /**
     * Based on count of notes in category (for recursive menu mostly)
     * @param int $category_id
     * @param string $type
     * @return bool
     * @throws DBALException
     */
    public function isNotesCategoryActive(int $category_id, string $type){

        switch ($type) {
            case 'MyNotes':
                return (bool) $this->app->repositories->myNotesRepository->countNotesInCategoryByCategoryId($category_id);
            default:
                return false;
        }
    }

    /**
     * @return array
     * @throws ExceptionDuplicatedTranslationKey
     * @throws DBALException
     */
    public function getAccessibleNotesCategories() {
        $accessible_categories = $this->my_notes_categories_controller->getAccessibleCategories();
        return $accessible_categories;
    }

    /**
     * @return array
     * @throws DBALException
     */
    public function getAllNotesCategories(){
        $all_categories = $this->my_notes_categories_controller->getAllNotesCategories();
        return $all_categories;
    }

    /**
     * @return array
     */
    public function buildNotesCategoriesDepths(): array {
        $depths = $this->my_notes_categories_controller->buildCategoriesDepths();
        return $depths;
    }

}