require('../global-before-env-driven');
let sharedLogin = require('../login/sharedLogin');
let sharedRestaurantMealCreation = require('../createRestaurantMeal/sharedRestaurantMealCreation');
let sharedBuyRestaurantMealWithoutCoupon = require('./sharedBuyRestaurantMealWithoutCoupon');

describe('Buy a restaurantmeal without coupon', function () {
    let mealId;
    let offerId;
    let mealPrice;

    sharedLogin.loginMMTestRestaurant();
    sharedRestaurantMealCreation.createRestaurantMeal();
    sharedLogin.loginMMTestGuest();
    sharedBuyRestaurantMealWithoutCoupon.buyRestaurantMealWithoutCoupon();
});