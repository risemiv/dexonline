<?php

function session_init() {
  // TODO: Optimize this. Load cookie first, then start session if necessary.
  if (util_isWebBasedScript()) {
    session_start();

    if (!session_userExists()) {
      session_loadUserFromCookie();
    }
  }
  // Otherwise we're being called by a local script, not a web-based one.
}

function session_login($user) {
  session_setUser($user);
  $cookie = new Cookie();
  $cookie->userId = $user->id;
  $cookie->cookieString = util_randomCapitalLetterString(12);
  $cookie->save();
  setcookie("prefs[lll]", $cookie->cookieString,
	    time() + ONE_MONTH_IN_SECONDS);
  log_userLog('Logged in, IP=' . $_SERVER['REMOTE_ADDR']);
  util_redirect(util_getWwwRoot());
}

function session_logout() {
  log_userLog('Logging out, IP=' . $_SERVER['REMOTE_ADDR']);
  $cookieString = session_getCookieSetting('lll');
  $cookie = Cookie::loadByCookieString($cookieString);
  $cookie->delete();
  setcookie("prefs[lll]", NULL, time() - 3600);
  unset($_COOKIE['prefs']['lll']);
  session_unset();
  session_destroy();
  util_redirect(util_getWwwRoot());
}

// Try to load loing information from the long-lived cookie
function session_loadUserFromCookie() {
  if (!isset($_COOKIE['prefs']) || !isset($_COOKIE['prefs']['lll'])) {
    return;
  }
  $user = User::loadByCookieString($_COOKIE['prefs']['lll']);
  if ($user) {
    session_setUser($user);
  } else {
    // There is a cookie, but it is invalid
    setcookie("prefs[lll]", NULL, time() - 3600);
    unset($_COOKIE['prefs']['lll']);
  }
}

function session_getCookieSetting($name) {
  if (array_key_exists('prefs', $_COOKIE)) {
    $prefsCookie = $_COOKIE['prefs'];
    if (array_key_exists($name, $prefsCookie)) {
      return $prefsCookie[$name];
    }
  }
  return FALSE;
}

function session_userExists() {
  return isset($_SESSION['user']) && isset($_SESSION['user']->id);
}

function session_userIsModerator() {
  return isset($_SESSION['user']) && isset($_SESSION['user']->moderator)
    && ($_SESSION['user']->moderator == 1);
}

function session_userIsFlexModerator() {
  $allowed = array("gall" => 1, "tavi" => 1, "raduborza" => 1, "cata" => 1);
  $user = session_getUser();
  return $user && array_key_exists($user->nick, $allowed);
}

function session_getUser() {
  if (!session_userExists()) {
    return FALSE;
  }

  return $_SESSION['user'];
}

function session_setUser($user) {
  $_SESSION['user'] = $user;
}

function session_getUserNick() {
  return isset($_SESSION['user']) && isset($_SESSION['user']->nick)
    ? $_SESSION['user']->nick : "Anonim";
}

function session_getUserId() {
  return isset($_SESSION['user']) && isset($_SESSION['user']->id)
    ? $_SESSION['user']->id : 0;
}

function session_getSkin() {
  $cookieSkin = session_getCookieSetting('skin');
  if ($cookieSkin && session_isValidSkin($cookieSkin)) {
    return $cookieSkin;
  } else {
    return pref_getDefaultSkin();
  }
}

function session_setSkin($skin) {
  $_COOKIE['prefs']['skin'] = $skin;
  session_sendSkinCookie();
}

function session_isValidSkin($skin) {
  return file_exists(util_getRootPath() . "templates/$skin") &&
    !strstr($skin, '/');
}

function session_sendSkinCookie() {
  setcookie('prefs[skin]', session_getSkin(), time() + 3600 * 24 * 365, "/");
}

function session_setSourceCookie($source) {
  setcookie('prefs[source]', $source, time() + 3600 * 24 * 365, "/");
}

function session_getDefaultContribSourceId() {
  $value = session_getCookieSetting('source');
  // Previously we stored some short name, not the source id -- just return
  // FALSE in that case
  return is_numeric($value) ? $value : FALSE;
}

function session_isDebug() {
  return session_getUserNick() == pref_getDebugUser();
}

?>
