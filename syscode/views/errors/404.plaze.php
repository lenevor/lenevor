<?= $this->extend('errors::layout') ?>

<?= $this->section('title') ?>

		<title><?= __('Not Found') ?></title>

<?= $this->stop() ?>

<?= $this->section('code') ?>

			<h1>404</h1>

<?= $this->stop() ?>

<?= $this->section('message') ?>

			<?= __($exception->getMessage() ?: 'Not Found') ?>

<?= $this->stop() ?>