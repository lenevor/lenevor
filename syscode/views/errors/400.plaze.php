<?= $this->extend('errors::layout') ?>

<?= $this->section('title') ?>

		<title><?= __('Bad Request') ?></title>

<?= $this->stop() ?>

<?= $this->section('code') ?>

				<h1>400</h1>

<?= $this->stop() ?>

<?= $this->section('message') ?>

				<?= __('Bad Request') ?>

<?= $this->stop() ?>