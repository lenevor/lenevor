<?= $this->extend('errors.layout') ?>

<?= $this->section('title') ?>

		<title><?= __('Request Entity Too Large') ?></title>

<?= $this->stop() ?>

<?= $this->section('code') ?>

			<h1>413</h1>

<?= $this->stop() ?>

<?= $this->section('message') ?>

			<?= __($exception->getMessage() ?: 'Request Entity Too Large') ?>

<?= $this->stop() ?>