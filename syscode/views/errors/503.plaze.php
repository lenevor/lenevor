<?= $this->extends('errors::layout') ?>

<?= $this->beginSection('title', 'Service Unavailable') ?>

<?= $this->beginSection('code', 503) ?>

<?= $this->beginSection('message', $exception->getMessage() ?: 'Service Unavailable') ?>