<?= $this->extend('errors::layout') ?>

<?= $this->section('title') ?>

		<title><?= __('Forbidden') ?></title>

<?= $this->stop() ?>

<?= $this->section('code') ?>

				<h1>403</h1>

<?= $this->stop() ?>

<?= $this->section('message') ?>

				<?= __($exception->getMessage() ?: 'Forbidden') ?>

<?= $this->stop() ?>