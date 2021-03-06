<?php

namespace App\Action\Modules\Notes;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Notes\MyNotes;
use App\Entity\Modules\Notes\MyNotesCategories;
use App\Services\Exceptions\ExceptionDuplicatedTranslationKey;
use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyNotesCategoriesAction extends AbstractController {

    const PARAMETER_ID = "id";

    /**
     * @var Application
     */
    private $app;

    /**
     * @var Controllers $controllers
     */
    private $controllers;

    public function __construct(Application $app, Controllers $controllers) {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("/my-notes/settings", name="my-notes-settings")
     * @param Request $request
     * @return Response
     * @throws DBALException
     * @throws ExceptionDuplicatedTranslationKey
     */
    public function display(Request $request) {
        $this->submitForm($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @Route("/my-notes/settings/remove/", name="my-notes-settings-remove")
     * @param Request $request
     * @return Response
     * @throws DBALException
     * @throws ExceptionDuplicatedTranslationKey
     */
    public function remove(Request $request) {

        $id = $request->request->get(self::PARAMETER_ID);

        $response = $this->app->repositories->deleteById(Repositories::MY_NOTES_CATEGORIES_REPOSITORY_NAME, $id);
        $message  = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderTemplate(true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }
        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("/my-notes/settings/update/",name="my-notes-settings-update")
     * @param Request $request
     * @return Response
     * @throws ExceptionDuplicatedTranslationKey
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $id         = $parameters[self::PARAMETER_ID];

        $entity     = $this->app->repositories->myNotesCategoriesRepository->find($id);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @param bool $ajax_render
     * @return Response
     * @throws DBALException
     */
    private function renderTemplate($ajax_render = false) {

        $form         = $this->app->forms->noteCategoryForm();
        $column_names = $this->getDoctrine()->getManager()->getClassMetadata(MyNotes::class)->getColumnNames();
        Repositories::removeHelperColumnsFromView($column_names);

        $categories = $this->app->repositories->myNotesCategoriesRepository->findActiveCategories();

        return $this->render('modules/my-notes/settings.html.twig',
            [
                'ajax_render'   => $ajax_render,
                'categories'    => $categories,
                'column_names'  => $column_names,
                'form'          => $form->createView()
            ]
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ExceptionDuplicatedTranslationKey
     */
    private function submitForm(Request $request) {
        $form = $this->app->forms->noteCategoryForm();
        $form->handleRequest($request);
        /**
         * @var MyNotesCategories $form_data
         */
        $form_data = $form->getData();

        if (!is_null($form_data) && $this->app->repositories->myNotesCategoriesRepository->findBy([MyNotesCategories::KEY_NAME => $form_data->getName()])) {
            $record_with_this_name_exist = $this->app->translator->translate('db.recordWithThisNameExist');
            return new JsonResponse($record_with_this_name_exist, 409);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->app->em->persist($form_data);
            $this->app->em->flush();
        }

        $form_submitted_message = $this->app->translator->translate('forms.general.success');
        return new JsonResponse($form_submitted_message,200);
    }

}