var page = require('webpage').create(),
  Q = require("q"); 
  system = require('system'),
  url = system.args[1]; //достаем параметр, в котором передан наш url страницы, которую мы парсим

console.log(url);
page.open(url, function (status) {
  page.includeJs('https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js', function() {
    var title = page.evaluate(function() {
      return document.title;
    });
    console.log(title);
    console.log('Hi');
    phantom.exit();
  });  //подключаем jquery.js
});
