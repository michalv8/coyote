<?php

namespace Tests\Feature\Controllers\Job;

use Coyote\Coupon;
use Coyote\Job;
use Coyote\Plan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Faker\Factory;

class PaymentControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var Job
     */
    private $job;

    public function setUp(): void
    {
        parent::setUp();

        $this->job = factory(Job::class)->create(['is_publish' => false]);
    }

    public function testSubmitInvalidFormWithoutAnyData()
    {
        $response = $this->actingAs($this->job->user)->json('POST', '/Praca/Payment/' . $this->job->getUnpaidPayment()->id);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(
            ['payment_method']
        );
    }

    public function testSubmitInvalidForm()
    {
        $payment = $this->job->getUnpaidPayment();

        $response = $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            ['payment_method' => 'card', 'price' => $payment->plan->gross_price, 'enable_invoice' => true]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(
            ['name', 'number', 'exp', 'cvc', 'invoice.address', 'invoice.name', 'invoice.city', 'invoice.country_id', 'invoice.postal_code']
        );

        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'name' => ['Pole nazwa jest wymagane.'],
                'number' => ['Pole numer karty kredytowej jest wymagane.'],
                'cvc' => ['Pole CVC jest wymagane.'],
                'exp' => ['Pole data ważności karty jest wymagane.'],
                'invoice.name' => ['To pole jest wymagane.'],
                'invoice.address' => ['To pole jest wymagane.'],
                'invoice.city' => ['To pole jest wymagane.'],
                'invoice.postal_code' => ['To pole jest wymagane.'],
                'invoice.country_id' => ['To pole jest wymagane.'],
            ]
        ]);
    }

    public function testSubmitValidFormWithoutInvoice()
    {
        $payment = $this->job->getUnpaidPayment();

        $response = $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            [
                'payment_method' => 'card',
                'price' => $payment->plan->gross_price,
                'enable_invoice' => false,
                'number' => '4012001038443335',
                'name' => 'Jan Kowalski',
                'cvc' => '123',
                'exp' => '12/26'
            ]
        );

        $response->assertStatus(200);
        $url = $response->getContent();

        $response = $this->get($url);

        $response->assertSee($this->job->title);
    }

    public function testSubmitFormWithInvalidExpDate()
    {
        $payment = $this->job->getUnpaidPayment();

        $response = $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            [
                'payment_method' => 'card',
                'price' => $payment->plan->gross_price,
                'enable_invoice' => false,
                'number' => '4012001038443335',
                'name' => 'Jan Kowalski',
                'cvc' => '123',
                'exp' => '12/19'
            ]
        );

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'message' => 'The given data was invalid.',
            'errors' => [
                'exp' => ['Upłynęła data ważności karty.']
            ]
        ]);
    }

    public function testSubmitValidFormWithInvoice()
    {
        $faker = Factory::create();
        $payment = $this->job->getUnpaidPayment();

        $response = $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            [
                'payment_method' => 'card',
                'price' => $payment->plan->gross_price,
                'enable_invoice' => true,
                'number' => '4012001038443335',
                'name' => 'Jan Kowalski',
                'cvc' => '123',
                'exp' => '12/26',
                'invoice' => [
                    'name' => $name = $faker->company,
                    'vat_id' => $vat = '123123123',
                    'country_id' => $countryId = 14,
                    'address' => $address = $faker->address,
                    'city' => $city = $faker->city,
                    'postal_code' => $postalCode = $faker->postcode
                ]
            ]
        );

        $response->assertStatus(200);
        $url = $response->getContent();

        $response = $this->get($url);

        $response->assertSee($this->job->title);

        $payment->refresh();

        $this->assertEquals($payment->invoice->name, $name);
        $this->assertEquals($payment->invoice->vat_id, $vat);
        $this->assertEquals($payment->invoice->country_id, $countryId);
        $this->assertEquals($payment->invoice->address, $address);
        $this->assertEquals($payment->invoice->city, $city);
        $this->assertEquals($payment->invoice->postal_code, $postalCode);
    }

    public function testSubmitFormWithCoupon()
    {
        $faker = Factory::create();

        $payment = $this->job->getUnpaidPayment();
        $payment->setRelation('plan', Plan::where('name', 'Premium')->get()->first());
        $payment->save();

        $coupon = Coupon::create(['amount' => 10, 'code' => $faker->randomAscii]);

        $response = $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            [
                'payment_method' => 'card',
                'price' => $payment->plan->gross_price,
                'enable_invoice' => false,
                'number' => '4012001038443335',
                'name' => 'Jan Kowalski',
                'cvc' => '123',
                'exp' => '12/26',
                'coupon' => $coupon->code
            ]
        );

        $response->assertStatus(200);
        $payment->refresh();

        $this->assertEquals($payment->invoice->grossPrice(), $payment->plan->gross_price - 10);
    }

    public function testSubmitFormWithTotalDiscount()
    {
        $faker = Factory::create();
        /** @var Plan $plan */
        $plan = Plan::where('name', 'Premium')->get()->first();

        $payment = $this->job->getUnpaidPayment();
        $payment->setRelation('plan', $plan);
        $payment->save();

        $coupon = Coupon::create(['amount' => $plan->gross_price, 'code' => $faker->randomAscii]);

        $response = $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            [
                'payment_method' => 'card',
                'price' => 0,
                'enable_invoice' => false,
                'coupon' => $coupon->code
            ]
        );

        $response->assertStatus(200);
        $payment->refresh();

        $this->assertEquals($payment->invoice->grossPrice(), 0);
    }

    public function testSubmitFormWithTransferMethod()
    {
        $payment = $this->job->getUnpaidPayment();

        $response = $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            [
                'payment_method' => 'transfer',
                'price' => $payment->plan->gross_price,
                'enable_invoice' => false,
                'transfer_method' => 25
            ]
        );

        $response->assertStatus(200);
        $url = $response->getContent();

        $response = $this->get($url);

        $response->assertSee('Ładowanie');
    }
}