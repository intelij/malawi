document.addEventListener("DOMContentLoaded", () => {
    const stripeKey = document
        .querySelector('meta[name="stripe-key"]')
        ?.getAttribute("content");

    if (!stripeKey) return;

    const stripe = Stripe(stripeKey);
    const submitButton = document.getElementById("submit");
    const form = document.getElementById("registration-form");

    const elements = stripe.elements();
    const cardElement = elements.create("card", { hidePostalCode: true });
    cardElement.mount("#card-element");

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        submitButton.disabled = true;
        submitButton.textContent = "Processing...";

        const cardholderName = document.getElementById("cardholder-name").value;
        const postcode = document.getElementById("postcode").value;

        const response = await fetch(form.dataset.intentUrl, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                amount: document.getElementById("amount").value,
            }),
        });

        const data = await response.json();

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

        if (paymentIntent.status === "succeeded") {
            form.submit();
        }
    });
});
