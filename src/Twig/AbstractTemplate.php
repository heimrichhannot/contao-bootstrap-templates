<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TwigTemplatesBundle\Twig;

use HeimrichHannot\TwigTemplatesBundle\Event\BeforeRenderTwigTemplateEvent;
use HeimrichHannot\TwigTemplatesBundle\FrontendFramework\AbstractFrontendFramework;
use HeimrichHannot\UtilsBundle\Template\TemplateUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractTemplate
{
    protected $templateName;
    protected $templateData;
    protected $entity;

    /**
     * @var TemplateUtil
     */
    protected $templateUtil;
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var AbstractFrontendFramework
     */
    protected $frontendFramework;
    /**
     * @var array
     */
    protected $support;

    /**
     * AbstractTemplate constructor.
     */
    public function __construct(ContainerInterface $container, AbstractFrontendFramework $frontendFramework)
    {
        $this->templateUtil = $container->get('huh.utils.template');
        $this->eventDispatcher = $container->get('event_dispatcher');
        $this->container = $container;
        $this->frontendFramework = $frontendFramework;
    }

    abstract public function getType(): string;

    /**
     * Set the form entity, e.g. Widget, Module,...
     *
     *
     * @param $entity
     */
    public function setEntity($entity)
    {
        $this->prepareData($entity);
        $this->entity = $entity;
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Render the widget.
     *
     * Uses $this->templateName and $this->templateData
     *
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return string
     */
    public function render()
    {
        $this->frontendFramework->compile($this->templateName, $this->templateData, $this);

        $event = $this->eventDispatcher->dispatch(
            BeforeRenderTwigTemplateEvent::NAME,
            new BeforeRenderTwigTemplateEvent($this->getType(), $this->templateName, $this->templateData, $this->entity)
        );

        return $this->templateUtil->renderTwigTemplate($event->getTemplateName(), $event->getTemplateData());
    }

    /**
     * Prepare templateName and templateData from entity (Widget, Module, ContentElement,...).
     *
     * @param $entity
     *
     */
    abstract protected function prepareData($entity);

    /**
     * Set if element support a feature
     *
     * @param string $key
     * @param mixed $value
     */
    public function addSupport(string $key, $value)
    {
        $this->support[$key] = $value;
    }

    /**
     * Check if element supports a feature
     * Return false, if support is not set or false.
     *
     * @param string $key
     * @return bool
     */
    public function hasSupport(string $key)
    {
        return (isset($this->support[$key]) && $this->support !== false);
    }

    /**
     * Get the value for a support feature.
     * Return false, if feature not found.
     *
     * @param string $key
     * @return bool|mixed
     */
    public function getSupport(string $key)
    {
        if (isset($this->support[$key]))
        {
            return $this->support[$key];
        }
        return false;
    }
}
