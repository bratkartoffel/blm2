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

class LoginTests extends AbstractTest {
    private final int userId = getNextUserId();

    @BeforeEach
    void beforeEach() {
        resetPlayer(userId, getClass().getSimpleName());
    }

    @Test
    void testLogin() {
        login("test" + userId);
    }

    @Test
    void testUnknownUser() {
        login("NonExistant", "changeit");
        assertElementPresent(By.id("meldung_108"));
    }

    @Test
    void testWrongPassword() {
        login("test" + userId, "wrongPassword");
        assertElementPresent(By.id("meldung_108"));
    }
}
