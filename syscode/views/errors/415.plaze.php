<?= $this->extend('errors.layout') ?>

<?= $this->section('title') ?>

		<title><?= __('Unsupported Media Type') ?></title>

<?= $this->stop() ?>

<?= $this->section('code') ?>

			<h1>415</h1>

<?= $this->stop() ?>

<?= $this->section('message') ?>

			<?= __($exception->getMessage() ?: 'Unsupported Media Type') ?>

<?= $this->stop() ?>