<@extends('errors::layout')

<@section('title', $exception->getTitle())  {{-- Title: Conflict --}} 
<@section('code', $exception->getStatusCode())  {{-- Code: 409 --}} 
<@section('message', $exception->getMessage())
