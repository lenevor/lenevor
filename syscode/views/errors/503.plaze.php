<?= $this->extend('errors.layout') ?>

<?= $this->section('title') ?>

		<title><?= __('Service Unavailable') ?></title>

<?= $this->stop() ?>

<?= $this->section('code') ?>

				<h1>503</h1>

<?= $this->stop() ?>

<?= $this->section('message') ?>

				<?= __($exception->getMessage() ?: 'Service Unavailable') ?>

<?= $this->stop() ?>