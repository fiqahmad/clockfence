<?php

test('the home page sends guests to log in', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('login'));
});
