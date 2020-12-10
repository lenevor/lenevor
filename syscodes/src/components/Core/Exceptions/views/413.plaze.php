<@extends('errors::layout')

<@section('title', $exception->getTitle())  {{-- Title: Request Entity Too Large --}}
<@section('code', $exception->getStatusCode())  {{-- Code: 413 --}} 
<@section('message', $exception->getMessage())
