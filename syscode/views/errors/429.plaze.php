<?= $this->extends('errors::layout') ?>

<?= $this->beginSection('title', 'Too Many Requests') ?>

<?= $this->beginSection('code', 429) ?>

<?= $this->beginSection('message', 'Too Many Requests') ?>