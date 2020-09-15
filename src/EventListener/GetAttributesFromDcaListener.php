<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\TwigTemplatesBundle\EventListener;

use Contao\Config;
use HeimrichHannot\UtilsBundle\Date\DateUtil;
use Symfony\Component\Translation\TranslatorInterface;

class GetAttributesFromDcaListener
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var DateUtil
     */
    private $dateUtil;

    /**
     * RenderListener constructor.
     */
    public function __construct(TranslatorInterface $translator, DateUtil $dateUtil)
    {
        $this->translator = $translator;
        $this->dateUtil = $dateUtil;
    }

    public function __invoke(array $attributes, $dc = null): array
    {
        // add format placeholder for accessibility reasons
        if ('text' === $attributes['type'] && isset($attributes['rgxp']) && \in_array($attributes['rgxp'], ['datim', 'date', 'time'])) {
            $attributes['placeholder'] = $this->translator->trans('huh.twig.templates.placeholder.'.$attributes['rgxp'], [
                '{format}' => $this->dateUtil->transformPhpDateFormatToISO8601(Config::get($attributes['rgxp'].'Format')),
            ]);
        }

        return $attributes;
    }
}
