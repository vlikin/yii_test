/**
 * casperjs --ssl-protocol=tlsv1 --cookies-file=cookies.txt \
 * ./login.casper.js \
 *   --login=+3806******* \
 *   --password=**** \
 *   --amount=1 \
 *   --phone=+3809********
 */

var system = require('system');
var fs = require('fs');

var index = 0;

var casper = require('casper').create({
  verbose: true,
  logLevel: 'debug'
});
casper.userAgent('Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0');

var login = casper.cli.get('login');
var password = casper.cli.get('password');
var phone = casper.cli.get('phone');
var amount = casper.cli.get('amount');
var time_code = Date.now();

var str_output = '------\n'
  + 'Login: ' + login + '\n'
  + 'Password: ' + password + '\n'
  + 'Phone: ' + phone + '\n'
  + 'Amount: ' + amount + '\n'
  + 'Time: ' + time_code + '\n'
  + '-------\n';
casper.echo(str_output);

var img_folder = 'img/  ' + login + '_' + time_code;
fs.makeDirectory(img_folder);

casper.on('resource.requested', function(request) {
  //this.echo(JSON.stringify(request, null, 4));
});

casper.start('https://qiwi.com', function() {
  // Login.
  this.fill(
    'form[role=login]',
    {
      login: login,
      password: password
    },
    false
  );
  this.capture(img_folder + '/' + (index++) + '-empty_login_form.png');
  this.click('form[role=login] .btn-signin');
  this.capture(img_folder + '/' + (index++) + '-filled_filled_form.png');
});

casper.waitFor(
  function check() {
    this.wait(2000);
    messages = this.evaluate(function() {
      return __utils__.findOne('#messages');
    });
    if (messages) {
      this.answer = {
        success: false,
        message: 'Authentication problem'
      }
      this.echo(messages);
      this.die('Authentication problem');
    }

    return this.getCurrentUrl() != 'https://qiwi.com/';
  },
  function then() {
    this.capture(img_folder + '/' + (index++) + '-login_form_redirected.png');
  },
  function timeout() {
    this.capture(img_folder + '/' + (index++) + '-login_form_timeout.png');
    this.echo('Request timeout.').exit();
  }
);
casper.then(function() {
  this.wait(2000);
  this.capture(img_folder + '/' + (index++) + '-login_form_redirected_1.png');
});
casper.thenOpen('https://qiwi.com/payment.action', function() {
  this.wait(2000);
  this.capture(img_folder + '/' + (index++) + '-payment_methods.png');
});
casper.thenOpen('https://qiwi.com/transfer.action', function() {
  this.wait(2000);
  this.capture(img_folder + '/' + (index++) + '-payment_methods.png');
});
casper.thenOpen('https://qiwi.com/transfer/form.action', function() {
  this.wait(2000);
  this.capture(img_folder + '/' + (index++) + '-empty_payment_form.png');
  this.fill(
    'form.payment_frm',
    {
      "extra['account']": phone,
      "amountInteger": amount
    },
    false
  );
  this.capture(img_folder + '/' + (index++) + '-filled_payment_form.png');
  this.click('form.payment_frm .orangeBtn');
})

casper.waitFor(
  function check() {
    this.wait(2000);
    get_orange_button = this.evaluate(function() {
      return __utils__.findOne('form.payment_frm .orangeBtn');
    });
    if (get_orange_button) {
      this.capture(img_folder + '/' + (index++) + '-confirmation_form.png');
      this.click('form.payment_frm .orangeBtn');
      return true;
    }
  },
  function then() {
    this.wait(2000);
    this.capture(img_folder + '/' + (index++) + '-confirmed.png');
  },
  function timeout() {
    this.capture(img_folder + '/' + (index++) + '-confirmation_form_timeout.png');
    this.echo('Confirmation request timeout.').exit();
  }
);

casper.waitFor(
  function check() {
    this.wait(2000);
    var verification_code = this.evaluate(function() {
      return __utils__.findOne('input[name=confirmationCode]');
    });
    get_orange_button = this.evaluate(function() {
      return __utils__.findOne('form.payment_frm .orangeBtn');
    });
    if (verification_code) {
      system.stdout.writeLine('Check your cell, write down the verification code: ');
      var code = system.stdin.readLine();
      this.fill(
        'form.payment_frm',
        {
          confirmationCode: code
        },
        false
       );
      this.capture(img_folder + '/' + (index++) + '-phone_confirmation_form_filled.png');
      this.click('form.payment_frm .orangeBtn');
      return true;
    }
  },
  function then() {
    this.wait(2000);
    this.capture(img_folder + '/' + (index++) + '-confirmed_by_phone.png');
  },
  function timeout() {
    this.capture(img_folder + '/' + (index++) + '-phone_confirmation_form_timeout.png');
    this.echo('Phone confirmation request timeout.').exit();
  }
);

casper.waitFor(
  function check() {
    this.wait(2000);
    var success = this.evaluate(function() {
      return __utils__.findOne('div.payment-success');
    });
    if (success) {
      this.capture(img_folder + '/' + (index++) + '-success.png');
      return true;
    }
  },
  function then() {
    this.wait(2000);
    this.capture(img_folder + '/' + (index++) + '-success-1.png');
  },
  function timeout() {
    this.capture(img_folder + '/' + (index++) + '-success_timeout.png');
    this.echo('Success request timeout.').exit();
  }
);

casper.run(function() {
  this.echo('Authentication process has been finished successfuly.').exit();
});
