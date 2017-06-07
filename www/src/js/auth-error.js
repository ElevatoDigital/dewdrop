// @todo this was auth-error.js
// make sure this works


// Maybe just scrap this?
import $ from 'jquery';
import Velocity from 'velocity-animate';
import VelocityUI from 'velocity-ui-pack';

class Auth {
    error() {
        Velocity($('.panel'), 'callout.shake');
    }
}

export default Auth;
