<@extends('errors::layout')

<@section('title', $exception->getTitle())  {{-- Title: Forbidden --}}
<@section('code', $exception->getStatusCode())  {{-- Code: 403 --}} 
<@section('message', $exception->getMessage())
