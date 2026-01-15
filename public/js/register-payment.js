document.addEventListener("DOMContentLoaded", () => {

    const stripeKey = document
        .querySelector('meta[name="stripe-key"]')
        ?.getAttribute("content");

    if (!stripeKey) {
        console.error("Stripe key not found");
        return;
    }

    const stripe = Stripe(stripeKey);

    const registrationForm = document.getElementById("registration-form");
    const submitButton = document.getElementById("submit");

    if (!registrationForm || !submitButton) {
        console.error("Form or submit button not found");
        return;
    }

    // 🔐 Guard to prevent infinite Stripe loop
    let stripePaymentCompleted = false;

    // Stripe Elements
    const elements = stripe.elements();
    const cardElement = elements.create("card", {
        hidePostalCode: true,
    });
    cardElement.mount("#card-element");

    registrationForm.addEventListener("submit", async (e) => {

        // ✅ If Stripe already completed, allow normal submit
        if (stripePaymentCompleted) {
            return;
        }

        e.preventDefault();

        submitButton.disabled = true;
        submitButton.textContent = "Processing...";

        try {
            const cardholderName =
                document.getElementById("cardholder-name")?.value?.trim();
            const postcode =
                document.getElementById("postcode")?.value?.trim();
            const amount =
                document.getElementById("amount")?.value;

            if (!cardholderName || !postcode || !amount) {
                throw new Error("Please complete all payment fields.");
            }

            console.log("1. We hit this");

            const formData = new FormData(registrationForm);

            // Add/override amount explicitly
            formData.set("amount", amount);

            const response = await fetch(
                registrationForm.dataset.intentUrl,
                {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                        "Accept": "application/json",
                    },
                    body: formData,
                }
            );

            // // 🔁 Create PaymentIntent
            // const response = await fetch(
            //     registrationForm.dataset.intentUrl,
            //     {
            //         method: "POST",
            //         headers: {
            //             "X-CSRF-TOKEN": document
            //                 .querySelector('meta[name="csrf-token"]')
            //                 .getAttribute("content"),
            //             "Content-Type": "application/json",
            //             "Accept": "application/json",
            //         },
            //         body: JSON.stringify({
            //             amount: amount,
            //             email: document.querySelector('[name="email"]').value,
            //             first_name: document.querySelector('[name="first_name"]').value,
            //             last_name: document.querySelector('[name="last_name"]').value,
            //         }),
            //     }
            // );

            console.log("2. We hit this", response);

            if (!response.ok) {
                throw new Error("Failed to create payment intent");
            }

            const data = await response.json();

            if (!data.clientSecret) {
                throw new Error("Invalid payment intent response");
            }

            console.log("3. We hit this");

            // 💳 Confirm payment
            const { error, paymentIntent } =
                await stripe.confirmCardPayment(data.clientSecret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: {
                            name: cardholderName,
                            address: {
                                postal_code: postcode,
                            },
                        },
                    },
                });

            if (error) {
                throw new Error(error.message);
            }

            console.log("4. We hit this", paymentIntent);

            // ✅ Payment success
            if (paymentIntent.status === "succeeded") {

                stripePaymentCompleted = true; // 🔥 critical

                // Inject paymentIntent ID into form
                const hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "payment_intent";
                hiddenInput.value = paymentIntent.id;
                registrationForm.appendChild(hiddenInput);

                // Final submit (safe)
                registrationForm.requestSubmit();
            }

        } catch (err) {
            console.error("Payment error:", err);

            alert(err.message || "Payment failed.");

            submitButton.disabled = false;
            submitButton.textContent = "Pay Now";
        }
    });
});
