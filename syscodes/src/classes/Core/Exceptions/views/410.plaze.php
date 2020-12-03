<@extends('errors::layout')

<@section('title', $exception->getTitle())  {{-- Title: Gone --}}
<@section('code', $exception->getStatusCode())  {{-- Code: 410 --}} 
<@section('message', $exception->getMessage())
