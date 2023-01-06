/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
package eu.fraho.blm2.st;

import org.junit.jupiter.api.Test;
import org.openqa.selenium.By;

class LoginTests extends AbstractTest {
    @Test
    void testLogin() {
        login("test1");
        assertElementPresent(By.id("meldung_202"));
    }

    @Test
    void testUnknownUser() {
        login("test33", "changeit");
        assertElementPresent(By.id("meldung_108"));
    }

    @Test
    void testWrongPassword() {
        login("test1", "wrong");
        assertElementPresent(By.id("meldung_108"));
    }
}
