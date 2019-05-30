<?= $this->extend('errors.layout') ?>

<?= $this->section('title') ?>

		<title><?= __('Gone') ?></title>

<?= $this->stop() ?>

<?= $this->section('code') ?>

			<h1>410</h1>

<?= $this->stop() ?>

<?= $this->section('message') ?>

			<?= __($exception->getMessage() ?: 'Gone') ?>

<?= $this->stop() ?>