<?= $this->extend('errors::layout') ?>

<?= $this->section('title') ?>

		<title><?= __('Precondition Failed') ?></title>

<?= $this->stop() ?>

<?= $this->section('code') ?>

			<h1>412</h1>

<?= $this->stop() ?>

<?= $this->section('message') ?>

			<?= __('Precondition Failed') ?>

<?= $this->stop() ?>