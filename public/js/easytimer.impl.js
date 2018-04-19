var diff = Math.abs(new Date("03-31-2019") - new Date()) / 1000;

var diffSecondTenths = Math.floor(diff/10);
var diffMins = Math.floor(diff/60);
var diffHours = Math.floor(diffMins/60);
var diffDays = Math.floor(diffHours/24);

var timer = new Timer();
timer.start({
    precision: 'secondTenths',
    countdown: true,
    startValues: {
        hours: diffHours, minutes: diffMins, seconds: diff, secondTenths: diffSecondTenths
    }
});

timer.addEventListener('secondTenthsUpdated', function (e) {
    $('#secondTenthsExample .values').html(
        'До наступної дати лишилось:<br/> ' + diffDays + ' днів ' +
        timer.getTimeValues().hours + ' годин ' +
        timer.getTimeValues().minutes + ' хвилин ' +
        timer.getTimeValues().seconds + ' секунд ');
});
