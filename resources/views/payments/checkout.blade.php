@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Checkout</h2>

    <form id="payment-form">
        @csrf
        <div class="mb-3">
            <label for="cardholder-name" class="form-label">Cardholder Name</label>
            <input type="text" id="cardholder-name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="postcode" class="form-label">Postcode</label>
            <input type="text" id="postcode" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="card-element" class="form-label">Card Details</label>
            <div id="card-element" class="form-control"></div>
        </div>

        <button id="submit" class="btn btn-primary mt-3 w-100">Pay £20</button>
    </form>

    <div id="payment-message" class="mt-3 text-success" style="display:none;"></div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe("{{ config('services.stripe.key') }}");

    fetch("{{ route('payment.intent') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ amount: 700 }) // £20 example
    })
    .then(res => res.json())
    .then(data => {
        const elements = stripe.elements();

        // Disable default postal/zip field from Stripe UI
        const cardElement = elements.create("card", {
            hidePostalCode: true
        });
        cardElement.mount("#card-element");

        const form = document.getElementById("payment-form");
        const submitButton = document.getElementById("submit");

        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            // disable button to prevent double clicks
            submitButton.disabled = true;
            submitButton.textContent = "Processing...";

            const cardholderName = document.getElementById("cardholder-name").value.trim();
            const postcode = document.getElementById("postcode").value.trim();

            if (!cardholderName || !postcode) {
                alert("Please enter both cardholder name and postcode.");
                submitButton.disabled = false;
                submitButton.textContent = "Pay £20";
                return;
            }

            const {error, paymentIntent} = await stripe.confirmCardPayment(data.clientSecret, {
                payment_method: {
                    card: cardElement,
                    billing_details: {
                        name: cardholderName,
                        address: {
                            postal_code: postcode
                        }
                    }
                }
            });

            if (error) {
                alert(error.message);
                submitButton.disabled = false;
                submitButton.textContent = "Pay £20";
            } else {
                window.location.href = "{{ route('payment.success') }}?paymentIntent=" + paymentIntent.id;
            }
        });
    });
</script>
@endsection
