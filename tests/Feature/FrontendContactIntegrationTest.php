<?php

namespace Tests\Feature;

use App\Mail\NewContactSubmission;
use App\Models\Business;
use App\Models\BusinessHour;
use App\Models\Contact;
use App\Models\ContactInfo;
use App\Models\Location;
use App\Models\Page;
use App\Models\SeoSetting;
use App\Models\SocialLink;
use App\Models\SpecialBusinessHour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FrontendContactIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SeoSetting::clearCache();
    }

    protected function tearDown(): void
    {
        SeoSetting::clearCache();

        parent::tearDown();
    }

    public function test_contact_page_renders_dynamic_business_location_hours_socials_and_html_rules(): void
    {
        Page::create($this->pageData([
            'name' => 'Contact',
            'slug' => 'contact',
            'h1' => 'Dynamic Contact Heading',
            'intro_text' => 'Dynamic contact intro.',
        ]));
        $business = Business::create($this->businessData(['name' => 'Dynamic Store']));
        $location = Location::create($this->locationData($business, [
            'phone' => '+1 301 555 0199',
            'email' => 'store@example.com',
            'address_line_1' => '123 Dynamic Ave',
            'city' => 'Oxon Hill',
            'state' => 'MD',
            'postal_code' => '20745',
            'directions_url' => 'https://www.google.com/maps/search/?api=1&query=dynamic',
        ]));
        BusinessHour::create(['location_id' => $location->id, 'day_of_week' => 1, 'opens_at' => '09:00', 'closes_at' => '17:00']);
        SpecialBusinessHour::create(['location_id' => $location->id, 'date' => now()->addDay(), 'opens_at' => '10:00', 'closes_at' => '14:00', 'note' => 'Holiday']);
        SocialLink::create(['business_id' => $business->id, 'platform' => 'facebook', 'label' => 'Facebook', 'url' => 'https://facebook.com/store', 'is_active' => true]);
        ContactInfo::create([
            'title' => 'Configured Contact Title',
            'description' => 'Configured contact description.',
            'address_title' => 'Configured Address',
            'form_title' => 'Configured form title',
            'form_description' => 'Configured form description.',
            'recipient_email' => 'admin@example.com',
            'status' => true,
        ]);

        $html = $this->get('/contact')
            ->assertOk()
            ->assertSee('Dynamic Contact Heading')
            ->assertSee('Dynamic contact intro.')
            ->assertSee('Configured Address')
            ->assertSee('123 Dynamic Ave')
            ->assertSee('+1 301 555 0199')
            ->assertSee('store@example.com')
            ->assertSee('Monday')
            ->assertSee('09:00 - 17:00')
            ->assertSee('Holiday')
            ->assertSee('Facebook')
            ->assertSee('Configured form title')
            ->getContent();

        $this->assertSame(1, preg_match_all('/<h1\b/i', $html));
        $this->assertSame(0, preg_match_all('/<h[2-6]\b/i', $html));
        $this->assertSame(1, preg_match_all('/<title>/i', $html));
        $this->assertSame(1, preg_match_all('/<meta name="description"/i', $html));
        $this->assertSame(1, preg_match_all('/<link rel="canonical"/i', $html));
        $this->assertSame(1, preg_match_all('/<meta name="robots"/i', $html));
        $this->assertStringNotContainsString('.html', $html);
    }

    public function test_valid_contact_post_stores_record_redirects_and_sends_admin_notification(): void
    {
        Mail::fake();
        $business = Business::create($this->businessData());
        Location::create($this->locationData($business, ['email' => 'location@example.com']));
        ContactInfo::create(['title' => 'Contact', 'recipient_email' => 'admin@example.com', 'status' => true]);

        $this->post('/contact', $this->validPayload(), ['REMOTE_ADDR' => '10.0.0.2'])
            ->assertRedirect(route('frontend.contact'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contacts', [
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'phone' => '+8801774865115',
            'topic' => 'Phone Repair',
            'subject' => 'Screen question',
            'status' => 'new',
        ]);

        Mail::assertSent(NewContactSubmission::class, fn ($mail) => $mail->hasTo('admin@example.com'));
    }

    public function test_contact_validation_rejects_missing_invalid_and_short_fields(): void
    {
        $this->post('/contact', [], ['REMOTE_ADDR' => '10.0.0.3'])
            ->assertSessionHasErrors(['name', 'email', 'message']);

        $this->post('/contact', $this->validPayload([
            'email' => 'not-an-email',
            'message' => 'short',
        ]), ['REMOTE_ADDR' => '10.0.0.4'])
            ->assertSessionHasErrors(['email', 'message']);
    }

    public function test_honeypot_submission_is_safely_accepted_without_storing_or_mailing(): void
    {
        Mail::fake();

        $this->post('/contact', $this->validPayload([
            'contact_company' => 'Spam Bot LLC',
        ]), ['REMOTE_ADDR' => '10.0.0.5'])
            ->assertRedirect(route('frontend.contact'))
            ->assertSessionHas('success');

        $this->assertSame(0, Contact::count());
        Mail::assertNothingSent();
    }

    public function test_contact_post_is_rate_limited_without_throttling_get(): void
    {
        Mail::fake();

        $this->get('/contact')->assertOk();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/contact', $this->validPayload([
                'email' => "customer{$i}@example.com",
            ]), ['REMOTE_ADDR' => '10.0.0.6']);
        }

        $this->post('/contact', $this->validPayload([
            'email' => 'customer6@example.com',
        ]), ['REMOTE_ADDR' => '10.0.0.6'])->assertTooManyRequests();
    }

    public function test_admin_contact_workflow_and_xss_escaping(): void
    {
        $contact = Contact::create([
            'name' => '<script>alert(1)</script>',
            'email' => 'customer@example.com',
            'phone' => '555',
            'topic' => 'General Question',
            'subject' => '<script>bad()</script>',
            'message' => '<script>alert(1)</script> Hello message.',
            'status' => 'new',
            'submitted_at' => now(),
        ]);

        $this->get(route('admin.contacts.index'))->assertRedirect('/login');

        $user = User::factory()->create(['email' => config('admin.email')]);
        $html = $this->actingAs($user)->get(route('admin.contacts.show', $contact))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->getContent();

        $this->assertStringContainsString('read', Contact::find($contact->id)->status);
        $this->assertStringContainsString('&lt;script&gt;', $html);

        $this->actingAs($user)
            ->put(route('admin.contacts.update', $contact), ['status' => 'replied'])
            ->assertSessionHasNoErrors();

        $this->assertSame('replied', $contact->fresh()->status);
        $this->assertNotNull($contact->fresh()->replied_at);

        $this->actingAs($user)
            ->put(route('admin.contacts.update', $contact), ['status' => 'not-valid'])
            ->assertSessionHasErrors('status');
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'phone' => '+8801774865115',
            'topic' => 'phone-repair',
            'subject' => 'Screen question',
            'message' => 'I need help with a phone repair question.',
            'contact_company' => null,
        ], $overrides);
    }

    private function pageData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Page',
            'slug' => 'page-'.uniqid(),
            'template' => 'default',
            'h1' => 'Page Heading',
            'intro_text' => null,
            'robots_index' => true,
            'robots_follow' => true,
            'is_home' => false,
            'status' => Page::STATUS_PUBLISHED,
            'published_at' => now(),
        ], $overrides);
    }

    private function businessData(array $overrides = []): array
    {
        return array_merge([
            'name' => '24/7 IN N OUT',
            'short_name' => '24/7 IN N OUT',
            'primary_category' => 'Convenience store',
            'is_active' => true,
        ], $overrides);
    }

    private function locationData(Business $business, array $overrides = []): array
    {
        return array_merge([
            'business_id' => $business->id,
            'name' => 'Primary Store',
            'slug' => 'primary-store',
            'address_line_1' => '6168 Oxon Hill Rd',
            'city' => 'Oxon Hill',
            'state' => 'MD',
            'postal_code' => '20745',
            'country_code' => 'US',
            'is_primary' => true,
            'is_active' => true,
        ], $overrides);
    }
}
