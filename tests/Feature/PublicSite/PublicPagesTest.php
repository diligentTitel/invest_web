<?php

namespace Tests\Feature\PublicSite;

use App\Models\ContactInquiry;
use App\Models\InvestmentCategory;
use App\Models\InvestmentOpportunity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_and_public_information_pages_render(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee('Know the structure')
            ->assertSee('Welcome. Begin with context.')
            ->assertSee('Create an account')
            ->assertSee('Create account')
            ->assertSee('data-story-chapter="landscape"', false);
        $this->get(route('register'))->assertOk()->assertSee('Create your account');
        $this->get(route('about'))->assertOk()->assertSee('A wider view of investing');
        $this->get(route('how-it-works'))->assertOk()->assertSee('How the platform is intended to work');
        $this->get(route('payment-methods'))->assertOk()->assertSee('No deposits, withdrawals');
        $this->get(route('faq'))->assertOk()->assertSee('Frequently asked questions');
        $this->get(route('terms'))->assertOk()->assertSee('WORKING DRAFT');
        $this->get(route('privacy'))->assertOk()->assertSee('WORKING DRAFT');
        $this->get(route('risk-disclosure'))->assertOk()->assertSee('Investment risk disclosure');
        $this->get(route('aml-kyc'))->assertOk()->assertSee('WORKING DRAFT');
    }

    public function test_catalog_does_not_publish_unreviewed_or_undocumented_opportunities(): void
    {
        $category = InvestmentCategory::query()->create([
            'name' => 'Real estate',
            'slug' => 'real-estate',
        ]);
        InvestmentOpportunity::query()->create([
            'category_id' => $category->id,
            'title' => 'Unreviewed property',
            'slug' => 'unreviewed-property',
            'description' => 'This listing must remain private.',
            'legal_review_status' => 'not_reviewed',
            'availability_status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('opportunities.index'));

        $response->assertOk()
            ->assertSee('We’re building the catalog with care.')
            ->assertDontSee('Unreviewed property');
    }

    public function test_contact_form_persists_inquiry_with_validation(): void
    {
        $this->get(route('contact'))->assertOk()->assertSee('t.me/JaredBirchall673');

        $response = $this->post(route('contact.store'), [
            'name' => 'Taylor Example',
            'email' => 'taylor@example.test',
            'topic' => 'Privacy or security',
            'message' => 'I have a question about the development platform.',
        ]);

        $response->assertRedirect(route('contact'));
        $this->assertDatabaseHas('contact_inquiries', [
            'email' => 'taylor@example.test',
            'topic' => 'Privacy or security',
            'status' => 'new',
        ]);
        $this->assertSame(1, ContactInquiry::query()->count());
    }
}