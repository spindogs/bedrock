<?php
use Timber\Timber;
use Timber\Post;
use Timber\PostQuery;

$data = Timber::get_context();
$data['post'] = new Post();
$data['posts'] = new PostQuery();
$data['fields'] = get_fields();

//Gets Post Categories
$args = array(
    'taxonomy' => 'category',
    'hide_empty' => true,
);

$categories = get_terms($args);
$data['categories'] = $categories;

Timber::render('archive.twig', $data);
