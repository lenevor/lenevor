<?= $this->extends('errors::layout') ?>

<?= $this->beginSection('title', 'Forbidden') ?>

<?= $this->beginSection('code', 403) ?>

<?= $this->beginSection('message', $exception->getMessage() ?: 'Forbidden') ?>