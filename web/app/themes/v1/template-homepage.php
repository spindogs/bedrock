<?php
/*
Template name: Homepage Template
*/

use Timber\Timber;
use Timber\Post;

$data = Timber::get_context();
$data['post'] = new Post();
$data['fields'] = get_fields();

Timber::render('template-homepage.twig', $data);
