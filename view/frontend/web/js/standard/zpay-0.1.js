
var $j = jQuery.noConflict();

var zpay_payments = {

	$zpay: null,
	orderId: null,
	address: null,
	amount: null,

	init: function() {
		// console.log('Zpay init');
		this.setGlobalVariables();
		this.setElements();
		this.api.init();
		this.bind();
	},

	setGlobalVariables: function() {
		if (typeof window.zpayConfig === 'undefined') {
			window.zpayConfig = {};
		}
		this.orderId = window.zpayConfig.orderId;
	},

	setElements: function() {
		this.$zpay = $j('.zpay-wrapper');
	},

	api: {
		options: null,
		con: null,
		conVerify: null,

		init: function() {
			this.setVariables();
			this.verifyValue();
		},

		setVariables: function() {
			this.options = {
				// render method: 'canvas', 'image' or 'div'
			    render: 'canvas',

			    // version range somewhere in 1 .. 40
			    minVersion: 10,
			    maxVersion: 40,

			    // error correction level: 'L', 'M', 'Q' or 'H'
			    ecLevel: 'L',

			    // offset in pixel if drawn onto existing canvas
			    left: 0,
			    top: 0,

			    // size in pixel
			    size: 300,

			    // code color or image element
			    fill: '#333333',

			    // background color or image element, null for transparent background
			    background: '#fff',

			    // content
			    text: '',

			    // corner radius relative to module width: 0.0 .. 0.5
			    radius: 0.3,

			    // quiet zone in modules
			    quiet: 2,

			    // modes
			    // 0: normal
			    // 1: label strip
			    // 2: label box
			    // 3: image strip
			    // 4: image box
			    mode: 0,

			    mSize: 0.1,
			    mPosX: 0.5,
			    mPosY: 0.5,

			    label: 'Zpay',
			    fontname: '"Raleway", "Helvetica Neue", Verdana, Arial, sans-serif',
			    fontcolor: '#ff9818',

			    image: null
			};

			this.con = window.zpayConfig.pay;
			this.conVerify = window.zpayConfig.verify;
		},

		timer: {

			timeinterval: null,
			$clock: null,
			endtime: null,

			initializeClock: function(){
				// console.log('Valendo timer!');
				this.endtime = new Date();
				this.endtime.setMinutes(this.endtime.getMinutes() + 15);
				this.$clock = $j('#clockdiv');
				this.updateClock();
				this.timeinterval = setInterval(this.updateClock, 1000);
			},

			updateClock: function() {
				var timer = zpay_payments.api.timer;
				var t = timer.getTimeRemaining();
				timer.$clock.html(t.minutes + ':' + t.seconds);
				if (t.total <= 1) {
					// console.log('Auto refresh do code');
					clearInterval(timer.timeinterval);
					zpay_payments.api.verifyValue();
				}
			},

			getTimeRemaining: function(){
				var t = Date.parse(this.endtime) - Date.parse(new Date());
				var seconds = Math.floor( (t/1000) % 60 );
				var minutes = Math.floor( (t/1000/60) % 60 );
				seconds = seconds + "";
				minutes = minutes + "";
				if (seconds.length < 2) seconds = "0" + seconds;
				if (minutes.length < 2) minutes = "0" + minutes;
				return {
					'total': t,
					'minutes': minutes,
					'seconds': seconds
				};
			}
		},

		verifyValue: function() {
			var amount = $j('.code-text').data('total');
			// console.log(amount);
			this.callApi(amount);
		},

		callApi: function(amount) {
			amount = parseFloat(amount);
			var con = this.con;
			var orderId = zpay_payments.orderId;
			var jqxhr = $j.post( con, {orderId: orderId, amount: amount})
								.done(function(data) {
					// console.log('retorno: ');
									// console.log(data);
									if (data.length > 0) {
										data = $j.parseJSON(data);
										zpay_payments.amount = data.amount_to;
										zpay_payments.address = data.address;
										zpay_payments.api.timer.initializeClock();
										$j('.zpay-wrapper .code-text p').text(zpay_payments.address);
										$j('.zpay-wrapper .btc').text(zpay_payments.amount);
										$j('.zpay-wrapper .rate').text( (amount / zpay_payments.amount).toFixed(2) );
										$j('.zpay-wrapper .orderId').text(data.order_id);
										zpay_payments.api.generateQR(zpay_payments.address);
										zpay_payments.api.verifyConfirm();
									}
								})
								.fail(function() {
									// console.log('não foi possível conectar');
									// return false;
								});
		},

		verifyConfirm: function() {
			if (zpay_payments.address.length > 0) {
				var timer = setInterval(function() {
					$j.post(zpay_payments.api.conVerify, {orderId: zpay_payments.orderId, address: zpay_payments.address})
							.done(function(data) {
								// console.log(data);
								if (data.length > 0) {
									data = $j.parseJSON(data);
									var status = '';
									status = data.order_status;

									if (status.length > 0) {
										// console.log(status);
										switch (status) {
											case "PROCESSING":
												if (data.payout_status == "PAID" ||
													data.payout_status == "OVERPAID") {
													// console.log('Pagamento confirmado, mensagem para o usuário.');
													zpay_payments.confirmed();
													break;
												}
											default:
												// console.log('deu merda');
												break;
										}
									} else {
										// console.log('Ops, não tem mensagem de status.');
									}
								}
							});
					}, 5000);
			}
		},

		generateQR: function(address) {
			// bitcoin:{{address}}?amount={{amount}}
			var amount = zpay_payments.amount;
			this.options.text = "bitcoin:" + address + "?amount=" + amount;
			canvas = $j('#zpay-code')[0];
			canvas.width = canvas.width;
			$j('#zpay-code').qrcode(this.options);
		}
	},

	confirmed: function() {
		location.href = window.zpayConfig.verified;
	},

	bind: function() {
		this.$zpay
			.on('click', '.force-refresh', function(evt) {
				evt.preventDefault();
				zpay_payments.api.verifyValue();
			});
	}
};


$j(document).ready(function() {
	zpay_payments.init();
});
