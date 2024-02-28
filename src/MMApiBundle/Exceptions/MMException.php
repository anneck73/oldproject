<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle\Exceptions;

/**
 * MMException is the base exception class for the Mealmatch WebApp.
 *
 * @todo: Think about transporting notices/errors to WebApp-User
 */
abstract class MMException extends \Exception
{
    protected $flashNotice;
    protected $modalError;

    /**
     * @return mixed
     */
    public function getFlashNotice()
    {
        return $this->flashNotice;
    }

    /**
     * @param mixed $flashNotice
     *
     * @return MMException
     */
    public function setFlashNotice($flashNotice)
    {
        $this->flashNotice = $flashNotice;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getModalError()
    {
        return $this->modalError;
    }

    /**
     * @param mixed $modalError
     *
     * @return MMException
     */
    public function setModalError($modalError)
    {
        $this->modalError = $modalError;

        return $this;
    }
}
