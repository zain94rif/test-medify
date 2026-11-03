<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_guests_are_redirected_to_login_from_the_root_page()
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_guests_cannot_access_master_items_or_categories(): void
    {
        foreach ([
            '/master-items',
            '/master-items/search',
            '/master-items/export',
            '/master-items/form/new',
            '/item-categories',
            '/item-categories/search',
            '/item-categories/options',
            '/item-categories/form/new',
        ] as $path) {
            $this->get($path)->assertRedirect(route('login'));
        }
    }

    public function test_guest_navigation_does_not_show_protected_menu_links(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Master Items')
            ->assertDontSee('Kategori Items');
    }
}
