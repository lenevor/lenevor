<?= $this->extend('errors.layout') ?>

<?= $this->section('title') ?>

		<title><?= __('Unprocessable Entity') ?></title>

<?= $this->stop() ?>

<?= $this->section('code') ?>

			<h1>422</h1>

<?= $this->stop() ?>

<?= $this->section('message') ?>

			<?= __($exception->getMessage() ?: 'Unprocessable Entity') ?>

<?= $this->stop() ?>