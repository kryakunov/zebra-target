// Копировавание данных из textarea
function copy() {
  let textarea = document.getElementById("textarea");
  textarea.select();
  document.execCommand("copy");
}

// Копировавание данных из textarea
function copy2() {
  let textarea = document.getElementById("textarea2");
  textarea.select();
  document.execCommand("copy");
}


// Копировавание данных из textarea
function copy3() {
  let textarea = document.getElementById("textarea3");
  textarea.select();
  document.execCommand("copy");
}


// Копировавание данных из textarea
function copy4() {
  let textarea = document.getElementById("textarea4");
  textarea.select();
  document.execCommand("copy");
}


function datachange(var1, var2){
  var time_min = document.getElementById('time_min');
  var time_max = document.getElementById('time_max');
  time_min.value = var1;
  time_max.value = var2;
}

// Изменение цвета кнопки при нажатии
function change(){
  var btn = document.getElementById('btnMenu');
  btn.value = 'Пожалуйста, ожидайте';
  btn.style.backgroundColor = '#4169E1';
    
    var i = 0, howManyTimes = 10, ii = 0;
  function f() {
          
          if (ii == 4) {
              btn.value = 'Пожалуйста, ожидайте';
                 ii = 0;
              } else {
                 btn.value = btn.value + '.';
                ii++;
              }
          
      if( i < howManyTimes ){
          setTimeout( f, 500 );
      }
  }
  f();
}


// Изменение цвета кнопки при нажатии
function changes(n){
  var btn = document.getElementById('btnMenu' + n);
  btn.value = 'Пожалуйста, ожидайте';
  btn.style.backgroundColor = '#4169E1';
    
    var i = 0, howManyTimes = 10, ii = 0;
  function f() {
          
          if (ii == 4) {
              btn.value = 'Пожалуйста, ожидайте';
                 ii = 0;
              } else {
                 btn.value = btn.value + '.';
                ii++;
              }
          
      if( i < howManyTimes ){
          setTimeout( f, 500 );
      }
  }
  f();
}


