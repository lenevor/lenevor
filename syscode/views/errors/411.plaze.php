<?= $this->extend('errors.layout') ?>

<?= $this->section('title') ?>

		<title><?= __('Length Required') ?></title>

<?= $this->stop() ?>

<?= $this->section('code') ?>

			<h1>411</h1>

<?= $this->stop() ?>

<?= $this->section('message') ?>

			<?= __($exception->getMessage() ?: 'Length Required') ?>

<?= $this->stop() ?>