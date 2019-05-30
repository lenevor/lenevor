<?= $this->extend('errors.layout') ?>

<?= $this->section('title') ?>

		<title><?= __('Internal Server Error') ?></title>

<?= $this->stop() ?>

<?= $this->section('code') ?>

				<h1>500</h1>

<?= $this->stop() ?>

<?= $this->section('message') ?>

				<?= __($exception->getMessage() ?: 'Internal Server Error') ?>

<?= $this->stop() ?>