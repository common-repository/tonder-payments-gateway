<?php
global $woocommerce;

// -- Keys --
$installed_payment_methods_tonder = WC()->payment_gateways->get_available_payment_gateways();
$api_key_tonder = $installed_payment_methods_tonder['zplit'] -> api_key;

// -- Current cart's items --
$cart_tonder = $woocommerce->cart;
$cart_items_tonder = $cart_tonder->get_cart();

$cart_items_list_tonder = array();

foreach ( $cart_items_tonder as $cart_item_key => $cart_item ) {
    // Get the product details
    $description = $cart_item['data']->get_description();
    $quantity = $cart_item['quantity'];
    $price_unit = floatval($cart_item['data']->get_price());
    $discount = floatval($cart_item['line_subtotal']) - floatval($cart_item['line_total']);
    $taxes = $cart_item['line_tax'];
    $product_reference = $cart_item['product_id'];
    $name = $cart_item['data']->get_name();
    $amount_total = $cart_item['line_total'];

    // Add the cart item information to the cart items list array
    $cart_items_list_tonder[] = array (
        'description' => $description,
        'quantity' => $quantity,
        'price_unit' => $price_unit,
        'discount' => $discount,
        'taxes' => $taxes,
        'product_reference' => $product_reference,
        'name' => $name,
        'amount_total' => $amount_total,
    );
}

$cart_items_tonder = $cart_items_list_tonder;

$url_politicas_tonder = plugins_url('/assets/pdfs/politicas.pdf', dirname(__FILE__));
$url_terminos_tonder = plugins_url('/assets/pdfs/terminos.pdf', dirname(__FILE__));
$url_error_tonder = plugins_url('/assets/img/remove.png', dirname(__FILE__));

?>

    <fieldset class="container-tonder">
        <p class="p-card-tonder">Titular de la tarjeta</p>
        <div id="collectCardholderNameTonder" class="empty-div-tonder"></div>
        <p class="p-card-tonder"> Información de la tarjeta</p>
        <div id="collectCardNumberTonder" class="empty-div-tonder"></div>
        <div class="collect-row-tonder">
            <div id="collectExpirationMonthTonder" class="empty-div-dates-tonder"></div>
            <div id="collectExpirationYearTonder" class="empty-div-dates-tonder"></div>
            <div id="collectCvvTonder" class="empty-div-cvc-tonder"></div>
        </div>
        <div id="msgError"></div>
        <div>
            <p class="politics-p-tonder">
                Tus datos personales se utilizarán para procesar tu pedido, respaldar tu
                experiencia a través de este sitio web y otros fines descritos en nuestra
                <a  class="link-terms-tonder" href="<?php echo esc_url($url_politicas_tonder); ?>" target="_blank">política de privacidad</a>.
            </p>
            <br>
            <div class="container-politics-tonder">
                <input type="checkbox" id="acceptTonder" name="scales" checked>
                <label class="terms-label-tonder" for="scales">
                    He leído y estoy de acuerdo con los
                    <a class="link-terms-tonder" href="<?php echo esc_url($url_terminos_tonder); ?>" target="_blank">términos y condiciones</a>
                    de este sitio web.
                </label>
            </div>
        </div>
    </fieldset>

    <style>
        .error-custom-inputs-tonder{
            margin-left: 4px !important;
            margin-top: -28px !important;
            font-size: 11px !important;
            color: red !important;
        }

        .error-custom-inputs-little-tonder{
            margin-left: 4px !important;
            margin-top: -53px !important;
            font-size: 11px !important;
            color: red !important;
        }

        .container-tonder {
            width: 90% !important;
            font-family: "Arial", sans-serif !important;
            margin: 0 auto !important;
            border: none !important;
        }

        .p-card-tonder {
            font-weight: bold !important;
            font-size: 13px !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .payment_method_zplit {
            font-size: 16px !important;
            width: 100% !important;
        }

        .payment_method_zplit  label img {
            width: 68px !important;
            padding-left: 1px !important;
        }

        .container-politics-tonder {
            display: flex !important;
            align-items: center !important;
        }

        .politics-p-tonder {
            font-size: 13px !important;
            margin: 0 !important;
        }

        .terms-label-tonder {
            font-size: 12px !important;
            margin: 0 0 0 10px !important;
        }

        .collect-row-tonder {
            display: flex !important;
            justify-content: space-between !important;
            width: 100% !important;
        }

        .collect-row-tonder > div {
            width: calc(25% - 10px) !important;
        }

        .collect-row-tonder > div:last-child {
            width: 50% !important;
        }

        .empty-div-tonder {
            height: 65px !important;
        }

        .empty-div-dates-tonder {
            height: 90px !important;
        }

        .empty-div-cvc-tonder {
            height: 90px !important;
        }

        .reveal-view {
            margin-top: 0px !important;
        }

        .error-tonder-container-tonder{
            color: red !important;
            background-color: #FFDBDB !important;
            margin-bottom: 13px !important;
            font-size: 80% !important;
            padding: 8px 10px !important;
            border-radius: 10px !important;
            text-align: left !important;
        }

        .image-error-tonder {
            width: 14px !important;
            margin: -2px 5px !important;
        }

        .link-terms-tonder {
            color: black !important;
        }

        .link-terms-tonder:hover {
            text-decoration: None !important;
            color: black !important;
        }

        @media screen and (max-width: 600px) {
            .p-card-tonder {
                font-weight: bold !important;
                font-size: 13px !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .payment_method_zplit {
                font-size: 16px !important;
                width: 100% !important;
            }

            .payment_method_zplit  label img {
                display: none !important;
            }

            .empty-div-dates-tonder {
                height: 90px !important;
                width: 60px !important;
            }

            .empty-div-cvc-tonder {
                height: 90px !important;
                width: 130px !important;
            }

        }

    </style>

    <script>
        jQuery(document).ready(function() {
            var cardNumberElementTonder,
                cvvElementTonder,
                expiryMonthElementTonder,
                expiryYearElementTonder,
                cardHolderNameElementTonder;

            // Tokenization and payment
            async function getTokenization(){

                // --- Skyflow ---
                var checkboxTonder = document.getElementById("acceptTonder");
                checkboxTonder.checked = false;

                // Load inputs
                // Token
                const baseUrlTonder = 'https://app.tonder.io/api/v1/';
                var vaultdIdTonder;
                var vaultUrlTonder;
                var referenceTonder;
                var userKeyTonder;
                var businessPkTonder;
                var openpayMerchantIdTonder;
                var openpayPublicKeyTonder;
                var collectContainerTonder;

                // -- Business' details --
                try {
                    const responseBusinessTonder = await fetch(`${baseUrlTonder}payments/business/<?php echo esc_js($api_key_tonder); ?>`, {
                        headers: {
                            'Authorization': `Token <?php echo esc_js($api_key_tonder); ?>`
                        }
                    });
                    const dataBusinessTonder = await responseBusinessTonder.json();

                    // Response data
                    vaultdIdTonder = dataBusinessTonder.vault_id
                    vaultUrlTonder = dataBusinessTonder.vault_url
                    referenceTonder = dataBusinessTonder.reference
                    businessPkTonder = dataBusinessTonder.business.pk

                    // Openpay
                    openpayMerchantIdTonder = dataBusinessTonder.openpay_keys.merchant_id
                    openpayPublicKeyTonder = dataBusinessTonder.openpay_keys.public_key

                } catch (error) {
                    if (jQuery('#payment_method_zplit').is(':checked')) {
                        jQuery('#place_order').prop('disabled', true);
                    }

                    jQuery('form.checkout').off('click').on('change', 'input[name="payment_method"]', function() {
                        // If the custom payment method is selected
                        if (jQuery('#payment_method_zplit').is(':checked')) {
                            // Click on the WC button
                            jQuery('#place_order').prop('disabled', true);
                        } else {
                            jQuery('#place_order').prop('disabled', false);
                        }
                    });
                }


                    const skyflowTonder = await Skyflow.init({
                            vaultID: vaultdIdTonder,
                            vaultURL: vaultUrlTonder,
                            getBearerToken: () => {
                                return new Promise((resolve, reject) => {
                                    const Http = new XMLHttpRequest();
                                    Http.onreadystatechange = () => {
                                        if (Http.readyState === 4 && Http.status === 200) {
                                            const response = JSON.parse(Http.responseText);
                                            resolve(response.token);
                                        }
                                    };
                                    const url = `${baseUrlTonder}vault-token/`;
                                    Http.open('GET', url);
                                    Http.setRequestHeader('Authorization', `Token <?php echo esc_js($api_key_tonder); ?>`);
                                    Http.send();
                                });
                            },

                        options: {
                                logLevel: Skyflow.LogLevel.ERROR,
                                env: Skyflow.Env.PROD,
                            },
                    });


                    // Create collect Container.
                    collectContainerTonder = await skyflowTonder.container(Skyflow.ContainerType.COLLECT);
                    // Custom styles for collect elements.
                    const collectStylesOptionsTonder = {
                        inputStyles: {
                            base: {
                                border: '3px solid #eae8ee !important',
                                padding: '10px 14px !important',
                                borderRadius: '10px !important',
                                color: '#1d1d1d !important',
                                marginTop: '0px !important',
                                backgroundColor: 'white !important'
                            },
                            complete: {
                                color: '#4caf50 !important',
                            },
                            empty: {},
                            focus: {},
                            invalid: {
                                color: "red !important",
                                backgroundColor: "#FFDBDB !important"
                            },
                        },
                        labelStyles: {
                            base: {
                                fontSize: '16px !important',
                                fontWeight: 'bold !important',
                            },
                        },
                        errorTextStyles: {
                            base: {
                                color: "red !important",
                                fontSize: "0px !important"
                            },
                        },
                    };

                    cardNumberElementTonder = await collectContainerTonder.create({
                        table: 'cards',
                        column: 'card_number',
                        ...collectStylesOptionsTonder,
                        label: '',
                        placeholder: 'Número de tarjeta',
                        type: Skyflow.ElementType.CARD_NUMBER,
                    });
                    cardNumberElementTonder.mount('#collectCardNumberTonder');

                    cardNumberElementTonder.on(Skyflow.EventName.CHANGE, state => {
                        var tonderContainerNumber = document.getElementById("collectCardNumberTonder");
                        var existingErrorLabelCarHolderTonder = document.getElementById("errorNumberTonder");

                        if (existingErrorLabelCarHolderTonder) {
                            existingErrorLabelCarHolderTonder.remove();
                        }

                        if (!state.isValid){
                            var errorLabel = document.createElement("p");
                            errorLabel.classList.add("error-custom-inputs-tonder");
                            errorLabel.id = "errorNumberTonder";
                            errorLabel.textContent = "No válido";
                            tonderContainerNumber.appendChild(errorLabel);
                        }
                    });

                    cvvElementTonder = await collectContainerTonder.create({
                        table: 'cards',
                        column: 'cvv',
                        ...collectStylesOptionsTonder,
                        label: '',
                        placeholder: 'CVC',
                        type: Skyflow.ElementType.CVV,
                    });
                    cvvElementTonder.mount('#collectCvvTonder');

                    cvvElementTonder.on(Skyflow.EventName.CHANGE, state => {
                        var tonderContainerNumber = document.getElementById("collectCvvTonder");
                        var existingErrorCVVTonder = document.getElementById("errorCVVTonder");

                        if (existingErrorCVVTonder) {
                            existingErrorCVVTonder.remove();
                        }

                        if (!state.isValid){
                            var errorLabel = document.createElement("p");
                            errorLabel.classList.add("error-custom-inputs-little-tonder");
                            errorLabel.id = "errorCVVTonder";
                            errorLabel.textContent = "No válido";
                            tonderContainerNumber.appendChild(errorLabel);
                        }
                    });

                    expiryMonthElementTonder = await collectContainerTonder.create({
                        table: 'cards',
                        column: 'expiration_month',
                        ...collectStylesOptionsTonder,
                        label: '',
                        placeholder: 'MM',
                        type: Skyflow.ElementType.EXPIRATION_MONTH,
                    });
                    expiryMonthElementTonder.mount('#collectExpirationMonthTonder');

                    expiryMonthElementTonder.on(Skyflow.EventName.CHANGE, state => {
                        var tonderContainerNumber = document.getElementById("collectExpirationMonthTonder");
                        var existingErrorExpMonthTonder = document.getElementById("errorExpMonthTonder");

                        if (existingErrorExpMonthTonder) {
                            existingErrorExpMonthTonder.remove();
                        }

                        if (!state.isValid){
                            var errorLabel = document.createElement("p");
                            errorLabel.classList.add("error-custom-inputs-little-tonder");
                            errorLabel.id = "errorExpMonthTonder";
                            errorLabel.textContent = "No válido";
                            tonderContainerNumber.appendChild(errorLabel);
                        }
                    });

                    expiryYearElementTonder = await collectContainerTonder.create({
                        table: 'cards',
                        column: 'expiration_year',
                        ...collectStylesOptionsTonder,
                        label: '',
                        placeholder: 'AA',
                        type: Skyflow.ElementType.EXPIRATION_YEAR,
                    });
                    expiryYearElementTonder.mount('#collectExpirationYearTonder');

                    expiryYearElementTonder.on(Skyflow.EventName.CHANGE, state => {
                        var tonderContainerNumber = document.getElementById("collectExpirationYearTonder");
                        var existingErrorExpYearTonder = document.getElementById("errorExpYearTonder");

                        if (existingErrorExpYearTonder) {
                            existingErrorExpYearTonder.remove();
                        }

                        if (!state.isValid){
                            var errorLabel = document.createElement("p");
                            errorLabel.classList.add("error-custom-inputs-little-tonder");
                            errorLabel.id = "errorExpYearTonder";
                            errorLabel.textContent = "No válido";
                            tonderContainerNumber.appendChild(errorLabel);
                        }
                    });

                    // Custom max length of cardholder name
                    const lengthMatchRule = {
                        type: Skyflow.ValidationRuleType.LENGTH_MATCH_RULE,
                        params: {
                            max : 70
                        }
                    }

                    cardHolderNameElementTonder = await collectContainerTonder.create({
                        table: 'cards',
                        column: 'cardholder_name',
                        ...collectStylesOptionsTonder,
                        label: '',
                        placeholder: 'Nombre como aparece en la tarjeta',
                        type: Skyflow.ElementType.CARDHOLDER_NAME,
                        validations: [lengthMatchRule],
                    });

                    cardHolderNameElementTonder.mount('#collectCardholderNameTonder');

                    cardHolderNameElementTonder.on(Skyflow.EventName.CHANGE, state => {
                        var tonderContainerCardHolder = document.getElementById("collectCardholderNameTonder");
                        var existingErrorLabelCarHolderTonder = document.getElementById("errorCardHolderIdTonder");

                        if (existingErrorLabelCarHolderTonder) {
                            existingErrorLabelCarHolderTonder.remove();
                        }

                        if (!state.isValid){
                            var errorLabel = document.createElement("p");
                            errorLabel.classList.add("error-custom-inputs-tonder");
                            errorLabel.id = "errorCardHolderIdTonder";
                            errorLabel.textContent = "No válido";
                            tonderContainerCardHolder.appendChild(errorLabel);
                        }
                    });

                    // --- End skyflow ---

                    // --- Tokenization ---
                    var checkoutFormTonder = jQuery('form.woocommerce-checkout');

                    const getResponseTonder = async () => {
                        var pageContainerTonder = 'body';

                        jQuery(pageContainerTonder).block({
                            message: null,
                            overlayCSS: {
                                background: "#fff",
                                opacity: .6
                            }
                        })

                        // Disable button
                        jQuery('#place_order').prop('disabled', true);

                        // Data from checkout
                        var billingFirstName = jQuery('form.woocommerce-checkout').find('#billing_first_name').val();
                        var billingLastName = jQuery('form.woocommerce-checkout').find('#billing_last_name').val();
                        var billingCountry = jQuery('form.woocommerce-checkout').find('#billing_country').val();
                        var billingAddressOne = jQuery('form.woocommerce-checkout').find('#billing_address_1').val();
                        var billingCity = jQuery('form.woocommerce-checkout').find('#billing_city').val();
                        var billingState = jQuery('form.woocommerce-checkout').find('#billing_state').val();
                        var billingPostcode = jQuery('form.woocommerce-checkout').find('#billing_postcode').val();
                        var billingEmail = jQuery('form.woocommerce-checkout').find('#billing_email').val();
                        var billingPhone = jQuery('form.woocommerce-checkout').find('#billing_phone').val();

                        if (!billingFirstName || !billingLastName || !billingCountry ||
                            !billingAddressOne || !billingCity || !billingState || !billingPostcode ||
                            !billingEmail || !billingPhone) {
                            jQuery(pageContainerTonder).unblock();
                            var msgErrorDiv = document.getElementById("msgError");
                            msgErrorDiv.classList.add("error-tonder-container-tonder");
                            msgErrorDiv.innerHTML = "<img src='<?php echo esc_url($url_error_tonder); ?>' class='image-error-tonder' alt='image-error-tonder'> Verifica los campos obligatorios";
                            setTimeout(function() {
                                jQuery('#place_order').prop('disabled', false);
                                msgErrorDiv.classList.remove("error-tonder-container-tonder");
                                msgErrorDiv.innerHTML = "";
                            }, 3000);
                            return false
                        }

                        // Card
                        var cardTokensSkyflowTonder = null;
                        try {
                            const collectResponseSkyflowTonder = await collectContainerTonder.collect();
                            cardTokensSkyflowTonder = await collectResponseSkyflowTonder["records"][0]['fields'];
                        } catch (error) {
                            jQuery(pageContainerTonder).unblock();
                            var msgErrorDiv = document.getElementById("msgError");
                            msgErrorDiv.classList.add("error-tonder-container-tonder");
                            msgErrorDiv.innerHTML = "<img src='<?php echo esc_url($url_error_tonder); ?>' class='image-error-tonder' alt='image-error-tonder'> Por favor, verifica todos los campos de tu tarjeta";
                            setTimeout(function() {
                                jQuery('#place_order').prop('disabled', false);
                                msgErrorDiv.classList.remove("error-tonder-container-tonder");
                                msgErrorDiv.innerHTML = "";
                            }, 6000);
                            return false
                        }

                        // Terminos y condiciones
                        var checkboxTonder = document.getElementById("acceptTonder");
                        if (!checkboxTonder.checked) {
                            jQuery(pageContainerTonder).unblock();
                            var msgErrorDiv = document.getElementById("msgError");
                            msgErrorDiv.classList.add("error-tonder-container-tonder");
                            msgErrorDiv.innerHTML = "<img src='<?php echo esc_url($url_error_tonder); ?>' class='image-error-tonder' alt='image-error-tonder'> Necesitas aceptar los términos y condiciones";
                            setTimeout(function() {
                                jQuery('#place_order').prop('disabled', false);
                                msgErrorDiv.classList.remove("error-tonder-container-tonder");
                                msgErrorDiv.innerHTML = "";
                            }, 3000);
                            return false
                        }

                        try{
                            // Openpay
                            let deviceSessionIdTonder;
                            if (openpayMerchantIdTonder && openpayPublicKeyTonder) {
                                deviceSessionIdTonder = await openpayCheckoutTonder(
                                    openpayMerchantIdTonder, openpayPublicKeyTonder
                                );
                            }

                            // Check user
                            const jsonResponseUser = await clientRegisterTonder(billingEmail);
                            userKeyTonder = jsonResponseUser.token

                            // Create order
                            var orderItems = {
                                "business":`<?php echo esc_js($api_key_tonder); ?>`,
                                "client":userKeyTonder,
                                "billing_address_id": null,
                                "shipping_address_id": null,
                                "amount":<?php echo WC()->cart->total; ?>,
                                "status": "A",
                                "reference":referenceTonder,
                                "is_oneclick": true,
                                "items":<?php echo json_encode($cart_items_tonder); ?>
                            }
                            const jsonResponseOrder = await createOrderTonder(orderItems);

                            // Create payment
                            const now = new Date();
                            const dateString = now.toISOString();
                            var paymentItems = {
                                "business_pk":businessPkTonder,
                                "amount":<?php echo WC()->cart->total; ?>,
                                "date": dateString,
                                "order": jsonResponseOrder.id
                            }
                            const jsonResponsePayment = await createPaymentTonder(paymentItems);

                            // Checkout router
                            const routerItems = {
                                "card": cardTokensSkyflowTonder,
                                "name": cardTokensSkyflowTonder.cardholder_name,
                                "last_name": "",
                                "email_client": billingEmail,
                                "phone_number": billingPhone,
                                "id_product": "no_id",
                                "quantity_product": 1,
                                "id_ship": "0",
                                "instance_id_ship": "0",
                                "amount": <?php echo WC()->cart->total; ?>,
                                "title_ship": "shipping",
                                "description": "transaction",
                                "device_session_id": deviceSessionIdTonder ? deviceSessionIdTonder : null,
                                "token_id": "",
                                "order_id": jsonResponseOrder.id,
                                "business_id": businessPkTonder,
                                "payment_id": jsonResponsePayment.pk
                            }
                            const jsonResponseRouter = await createCheckoutRouterTonder(routerItems);

                            if (jsonResponseRouter) {
                                jQuery(pageContainerTonder).unblock();
                                jQuery('#place_order').prop('disabled', false);
                                return true
                            } else {
                                jQuery(pageContainerTonder).unblock();
                                var msgErrorDiv = document.getElementById("msgError");
                                msgErrorDiv.classList.add("error-tonder-container-tonder");
                                msgErrorDiv.innerHTML = "<img src='<?php echo esc_url($url_error_tonder); ?>' class='image-error-tonder' alt='image-error-tonder'> No se ha podido procesar el pago";
                                setTimeout(function() {
                                    jQuery('#place_order').prop('disabled', false);
                                    msgErrorDiv.classList.remove("error-tonder-container-tonder");
                                    msgErrorDiv.innerHTML = "";
                                }, 3000);
                                return false
                            }
                        } catch (error) {
                            jQuery(pageContainerTonder).unblock();
                            var msgErrorDiv = document.getElementById("msgError");
                            msgErrorDiv.classList.add("error-tonder-container-tonder");
                            msgErrorDiv.innerHTML = "<img src='<?php echo esc_url($url_error_tonder); ?>' class='image-error-tonder' alt='image-error-tonder'> Ha ocurrido un error";
                            setTimeout(function() {
                                jQuery('#place_order').prop('disabled', false);
                                msgErrorDiv.classList.remove("error-tonder-container-tonder");
                                msgErrorDiv.innerHTML = "";
                            }, 3000);
                            return false
                        }
                    }

                    if (jQuery('#payment_method_zplit').is(':checked')) {
                        // LocalStorage
                        localStorage.setItem('tonder', 'true');
                        // Click on the WC button
                        jQuery('#place_order').off('click').on('click', async function(e) {
                            // Prevent the form submission
                            e.preventDefault();
                            // Start the tokenization
                            const response = await getResponseTonder();
                            // Response
                            if (response == true){
                                checkoutFormTonder.submit();
                            }
                        });
                    }

                    jQuery('form.checkout').off('change').on('change', 'input[name="payment_method"]', function() {
                        // If the custom payment method is selected
                        if (jQuery('#payment_method_zplit').is(':checked')) {
                            // LocalStorage
                            localStorage.setItem('tonder', 'true');
                            // Click on the WC button
                            jQuery('#place_order').off('click').on('click', async function(e) {
                                // Prevent the form submission
                                e.preventDefault();
                                // Start the tokenization
                                const response = await getResponseTonder();
                                // Response
                                if (response == true){
                                    checkoutFormTonder.submit();
                                }
                            });
                        } else {
                            jQuery('#place_order').off('click');
                        }
                    });

                    // --- Request to backend ---
                    // -- Register user --
                    async function clientRegisterTonder(email) {
                        // Verify if the email is registered
                        const url = `${baseUrlTonder}client-existence/${email}`
                        const response = await fetch(url, {
                            headers: {
                                'Authorization': `Token <?php echo esc_js($api_key_tonder); ?>`
                            }
                        });
                        if (response.status === 200) {
                            const jsonResponse = await response.json();
                            if (jsonResponse.message === true) {
                                return await activation(email);
                            } else {
                                return await registration(email);
                            }
                        } else {
                            throw new Error(`Error: ${response.statusText}`);
                        }
                    };

                    async function registration(email) {
                        const url = `${baseUrlTonder}customer-register/`
                        const data = {
                            'email': email,
                            'password': '',
                            'repeat_password': ''
                        };
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Token <?php echo esc_js($api_key_tonder); ?>`
                            },
                            body: JSON.stringify(data)
                        });

                        if (response.status === 201) {
                            const jsonResponse = await activation(email);
                            return jsonResponse;
                        } else {
                            throw new Error(`Error: ${response.statusText}`);
                        }
                    }

                    async function activation(email) {
                        const url = `${baseUrlTonder}activate-customer/`
                        const data = {'email': email,};
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Token <?php echo esc_js($api_key_tonder); ?>`
                            },
                            body: JSON.stringify(data)
                        });
                        if (response.status === 200) {
                            const jsonResponse = await response.json();
                            return jsonResponse;
                        } else {
                            throw new Error(`Error: ${response.statusText}`);
                        }
                    }

                    // -- Create order --
                    async function createOrderTonder(orderItems) {
                        const url = `${baseUrlTonder}orders/`
                        const data = orderItems;
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Token <?php echo esc_js($api_key_tonder); ?>`
                            },
                            body: JSON.stringify(data)
                        });
                        if (response.status === 201) {
                            const jsonResponse = await response.json();
                            return jsonResponse;
                        } else {
                            throw new Error(`Error: ${response.statusText}`);
                        }
                    }

                    // -- Create payment --
                    async function createPaymentTonder(paymentItems) {
                        const url = `${baseUrlTonder}business/${paymentItems.business_pk}/payments/`
                        const data = paymentItems;
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Token <?php echo esc_js($api_key_tonder); ?>`
                            },
                            body: JSON.stringify(data)
                        });
                        if (response.status === 200) {
                            const jsonResponse = await response.json();
                            return jsonResponse;
                        } else {
                            throw new Error(`Error: ${response.statusText}`);
                        }
                    }

                    // -- Create payment with router --
                    async function createCheckoutRouterTonder(routerItems) {
                        const url = `${baseUrlTonder}checkout-router/`
                        const data = routerItems;
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Token <?php echo esc_js($api_key_tonder); ?>`
                            },
                            body: JSON.stringify(data)
                        });
                        if (response.status === 200) {
                            const jsonResponse = await response.json();
                            return true;
                        } else {
                            return false
                        }
                    }

                    // -- Openpay --
                    async function openpayCheckoutTonder(merchant_id, public_key) {
                        let openpay = await window.OpenPay;
                        openpay.setId(merchant_id);
                        openpay.setApiKey(public_key);
                        openpay.setSandboxMode(false);
                        var response = await openpay.deviceData.setup();
                        return response
                    };

                    // --- End request to backend ---

                    // --- End tokenization ---
            }

            // -- Init --
            jQuery(document.body).off('updated_cart_totals').on('updated_cart_totals', function() {
                const cardNumberContainer = document.querySelector('#collectCardNumberTonder')
                const cvvContainer = document.querySelector('#collectCvvTonder');
                const expiryMonthContainer = document.querySelector('#collectExpirationMonthTonder');
                const expiryYearContainer = document.querySelector('#collectExpirationYearTonder');
                const cardHolderNameContainer = document.querySelector('#collectCardholderNameTonder');

                if (
                    cardNumberContainer.querySelector('iframe') &&
                    cvvContainer.querySelector('iframe') &&
                    expiryMonthContainer.querySelector('iframe') &&
                    expiryYearContainer.querySelector('iframe') &&
                    cardHolderNameContainer.querySelector('iframe')
                ) {
                    return;
                }

                if (
                    cardNumberElementTonder &&
                    cvvElementTonder &&
                    expiryMonthElementTonder &&
                    expiryYearElementTonder &&
                    cardHolderNameElementTonder
                ) {
                    cardNumberElementTonder.unmount();
                    cvvElementTonder.unmount();
                    expiryMonthElementTonder.unmount();
                    expiryYearElementTonder.unmount();
                    cardHolderNameElementTonder.unmount();
                }

                getTokenization();
            });

            jQuery(document.body).trigger('updated_cart_totals');
        });

    </script>
<?php

