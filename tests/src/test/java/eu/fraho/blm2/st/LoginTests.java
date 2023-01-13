/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
package eu.fraho.blm2.st;

import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.openqa.selenium.By;

import java.util.concurrent.ThreadLocalRandom;

class LoginTests extends AbstractTest {
    private static final int USER_ID = ThreadLocalRandom.current().nextInt(1_000_000);

    @BeforeEach
    void beforeEach() {
        resetPlayer(USER_ID, getClass().getSimpleName());
    }

    @Test
    void testLogin() {
        login("test" + USER_ID);
    }

    @Test
    void testUnknownUser() {
        login("NonExistant", "changeit");
        assertElementPresent(By.id("meldung_108"));
    }

    @Test
    void testWrongPassword() {
        login("test" + USER_ID, "wrongPassword");
        assertElementPresent(By.id("meldung_108"));
    }
}
