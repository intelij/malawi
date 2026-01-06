document.addEventListener("DOMContentLoaded", () => {
    const stripeKey = document
        .querySelector('meta[name="stripe-key"]')
        ?.getAttribute("content");

    if (!stripeKey) return;

    const stripe = Stripe(stripeKey);

    const registrationForm = document.getElementById("registration-form");
    const submitButton = document.getElementById("submit");

    const elements = stripe.elements();
    const cardElement = elements.create("card", { hidePostalCode: true });
    cardElement.mount("#card-element");

    registrationForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        submitButton.disabled = true;
        submitButton.textContent = "Processing...";

        const cardholderName = document.getElementById("cardholder-name").value;
        const postcode = document.getElementById("postcode").value;
        const amount = document.getElementById("amount").value;

        try {
            const response = await fetch(
                registrationForm.dataset.intentUrl,
                {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                    },
                    body: JSON.stringify({
                        amount,
                        email: document.querySelector('[name="email"]').value,
                        first_name: document.querySelector('[name="first_name"]').value,
                        last_name: document.querySelector('[name="last_name"]').value,
                    }),
                }
            );

            const data = await response.json();

            console.log("Debug number 1");

            if (!data.clientSecret) {
                throw new Error("Failed to create payment intent");
            }

            console.log("Debug number 2");

            const { error, paymentIntent } =
                await stripe.confirmCardPayment(data.clientSecret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: {
                            name: cardholderName,
                            address: { postal_code: postcode },
                        },
                    },
                });

            if (error) {
                alert(error.message);
                submitButton.disabled = false;
                submitButton.textContent = "Pay Now";
                return;
            }

            console.log("Debug number 3");

            if (paymentIntent.status === "succeeded") {
                // 🔥 Inject paymentIntent ID into the form
                const hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "payment_intent_id";
                hiddenInput.value = paymentIntent.id;
                registrationForm.appendChild(hiddenInput);

                console.log("Debug number 3a");

                // ✅ Submit real HTML form
                registrationForm.submit();
            }

            console.log("Debug number 4");

        } catch (err) {
            console.error("Payment error:", err);
            alert(err.message || "Payment failed.");
            submitButton.disabled = false;
            submitButton.textContent = "Pay Now";
        }
    });
});
