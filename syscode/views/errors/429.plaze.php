<?= $this->extend('errors.layout') ?>

<?= $this->section('title') ?>

		<title><?= __('Too Many Requests') ?></title>

<?= $this->stop() ?>

<?= $this->section('code') ?>

			<h1>429</h1>

<?= $this->stop() ?>

<?= $this->section('message') ?>

			<?= __($exception->getMessage() ?: 'Too Many Requests') ?>

<?= $this->stop() ?>