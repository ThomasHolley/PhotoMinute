<?php

declare(strict_types=1);

namespace Galerie\Controller;

use Application\Tools\MainController;
use Galerie\Form\GalerieForm;
use Galerie\Model\Galerie;
use Laminas\View\Model\ViewModel;

/**
 *
 * This file has been generated with LaminasGen
 * https://github.com/ThomasLeconte/laminas-gen
 *
 */
class GalerieController extends MainController
{
    public function indexAction()
    {
        if ($this->isAuthenticated()) {
            return [
                'galeries' => $this->getRepository(Galerie::class)->findBy(["user" => $this->sessionValue("user")])
            ];
        } else {
            return $this->notAuthenticatedPage();
        }
    }

    public function addAction()
    {
        $form = new GalerieForm();
        $request = $this->getRequest();
        if ($request->isGet()) {
            return ["form" => $form];
        } else {
            $galerie = new Galerie();
            $form->setInputFilter($galerie->getInputFilter());
            $form->setData($request->getPost());
            if (!$form->isValid()) {
                return ["form" => $form];
            } else {
                $galerie->setNom($form->get("nom")->getValue());
                $galerie->setDescription($form->get("description")->getValue());
                $galerie->setUser($this->sessionValue("user"));
                $galerie->setCreated(new \DateTime());
                $galerie->setUpdated(new \DateTime());
                $this->entityManager->persist($galerie);
                $this->entityManager->flush();
                $this->redirect()->toUrl("/galerie");
            }
        }
    }

    public function editAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('galerie', ['action' => 'add']);
        }

        try {
            $galerie = $this->getRepository(Galerie::class)->find($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('photo', ['action' => 'index']);
        }

        $form = new GalerieForm();
        $form->get('submit')->setAttribute('value', 'Modifier');

        $request = $this->getRequest();
        $viewData = ['id' => $id, 'form' => $form, 'galerie' => $galerie];

        if (!$request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($galerie->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return $viewData;
        }

        $galerie->setNom($form->get('nom')->getValue());
        $galerie->setDescription($form->get('description')->getValue());
        $this->entityManager->persist($galerie);
        $this->entityManager->flush();

        // Redirect to photo list
        return $this->redirect()->toRoute('galerie', ['action' => 'index']);
    }

    public function deleteAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('galerie');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int)$request->getPost('id');
                $galerie = $this->getRepository(Galerie::class)->find($id);
                $this->entityManager->remove($galerie);
                $this->entityManager->flush();
            }

            // Redirect to list of photos
            return $this->redirect()->toRoute('galerie');
        }
        return [
            'id' => $id,
            'galerie' => $this->getRepository(Galerie::class)->find($id),
        ];
    }
}
