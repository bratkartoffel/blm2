/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
package eu.fraho.blm2.st;

import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.TestInfo;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;

import java.util.concurrent.ThreadLocalRandom;

public class ShopTests extends AbstractTest {
    private static final int USER_ID = ThreadLocalRandom.current().nextInt(1_000_000);

    @BeforeEach
    void beforeEach(TestInfo testInfo) {
        resetPlayer(USER_ID, testInfo);
        login("test" + USER_ID);
    }

    @Test
    void testSellAll() {
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_bioladen")).click();
        assertText(By.id("stat_money"), "5,000.00 €");
        driver.findElement(By.id("sell_all")).click();

        assertElementPresent(By.id("meldung_208"));
        assertText(By.id("stat_money"), "5,374.64 €");

        driver.findElement(By.id("link_buero")).click();
        assertText(By.id("b_i_1"), "374.64 €");
    }

    @Test
    void testSell() {
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_bioladen")).click();
        assertText(By.id("stat_money"), "5,000.00 €");
        setValue(By.id("amount_1"), "30");
        driver.findElement(By.id("sell_1")).click();

        assertElementPresent(By.id("meldung_208"));
        assertText(By.id("stat_money"), "5,054.60 €");
        assertText(By.id("cur_amount_1"), "70 kg");

        driver.findElement(By.id("sell_1")).click();
        assertText(By.id("stat_money"), "5,182.00 €");

        driver.findElement(By.id("link_buero")).click();
        assertText(By.id("b_i_1"), "182.00 €");
    }
}
