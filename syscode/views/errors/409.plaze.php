<?= $this->extend('errors::layout') ?>

<?= $this->section('title') ?>

		<title><?= __('Conflict') ?></title>

<?= $this->stop() ?>

<?= $this->section('code') ?>

			<h1>409</h1>

<?= $this->stop() ?>

<?= $this->section('message') ?>

			<?= __($exception->getMessage() ?: 'Conflict') ?>

<?= $this->stop() ?>