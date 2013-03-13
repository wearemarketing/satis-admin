<?php

namespace SatisAdmin\Controller;

use SatisAdmin\Form\ConfigType;
use SatisAdmin\Form\RepositoryType;
use SatisAdmin\Model\Repository;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
class DefaultController extends Controller
{
    /**
     * @var \SatisAdmin\Application
     */
    protected $app;

    /**
     * {@inheritDoc}
     */
    public function mount(ControllerCollection $controllers)
    {
        $controllers->get('/', [$this, 'indexAction'])->bind('config_index');
        $controllers->get('/edit', [$this, 'editAction'])->bind('config_edit');
        $controllers->post('/edit', [$this, 'updateAction'])->bind('config_update');
        $controllers
            ->get('/repository/{type}/form-fragment/{index}', [$this, 'retrieveRepositoryFormFragmentAction'])
            ->bind('retrieve_repository_form_fragment');
    }

    /**
     * @return string
     */
    public function indexAction()
    {
        return $this->render('default/index.html.twig', ['config' => $this->getModelManager()->getConfig()]);
    }

    /**
     * @return string
     */
    public function editAction()
    {
        return $this->render('default/edit.html.twig', ['form' => $this->getForm()->createView()]);
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function updateAction(Request $request)
    {
        $form = $this->getForm();
        $form->bind($request);
        if ($form->isValid()) {
            $this->getModelManager()->persist($form->getData());

            return $this->app->redirect($this->getRouter()->generate('config_index'));
        }

        return $this->render('default/edit.html.twig', ['form' => $form->createView()]);
    }

    public function retrieveRepositoryFormFragmentAction($type, $index)
    {
        $form = $this->getFormFactory()->createNamed('config');
        $form->add($this->getFormFactory()->createNamed('repositories'));
        $form['repositories']->add($this->getFormFactory()->createNamed($index, new RepositoryType));
        $form['repositories'][$index]->setData(Repository::create($type));

        return $this->render(
            'default/retrieveRepositoryFormFragment.html.twig',
            [
                'form' => $form['repositories'][$index]->createView()
            ]
        );
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function getForm()
    {
        return $this->getFormFactory()->create(new ConfigType, $this->getModelManager()->getConfig());
    }
}
