<@extends('errors::layout')

<@section('title', $exception->getTitle())  {{-- Title: Method Not Allowed --}}
<@section('code', $exception->getStatusCode())  {{-- Code: 405 --}} 
<@section('message', $exception->getMessage())
