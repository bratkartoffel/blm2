/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
package eu.fraho.blm2.st;

import org.junit.jupiter.api.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;

public class ShopTests extends AbstractTest {
    @Test
    void testSellAll() {
        resetPlayer(12);
        login("test2");
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_bioladen")).click();
        assertText(By.id("stat_money"), "5.000,00 €");
        driver.findElement(By.id("sell_all")).click();

        assertElementPresent(By.id("meldung_208"));
        assertText(By.id("stat_money"), "5.307,50 €");

        driver.findElement(By.id("link_buero")).click();
        assertText(By.id("b_i_1"), "307,50 €");
    }

    @Test
    void testSell() {
        resetPlayer(11);
        login("test1");
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_bioladen")).click();
        assertText(By.id("stat_money"), "5.000,00 €");
        setValue(By.id("amount_1"), "30");
        driver.findElement(By.id("sell_1")).click();

        assertElementPresent(By.id("meldung_208"));
        assertText(By.id("stat_money"), "5.054,60 €");
        assertText(By.id("cur_amount_1"), "70 kg");

        driver.findElement(By.id("sell_1")).click();
        assertText(By.id("stat_money"), "5.182,00 €");

        driver.findElement(By.id("link_buero")).click();
        assertText(By.id("b_i_1"), "182,00 €");
    }
}
