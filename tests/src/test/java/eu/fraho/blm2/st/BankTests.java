/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
package eu.fraho.blm2.st;

import org.junit.jupiter.api.Assertions;
import org.junit.jupiter.api.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;

public class BankTests extends AbstractTest {
    @Test
    void testDeposit() {
        resetPlayer(13);
        WebDriver driver = getDriver();
        login(driver, "test3");
        driver.findElement(By.id("link_bank")).click();

        // check current balance
        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 50.000,00 €");

        // deposit 5k
        setValue(By.id("betrag"), "5000");
        driver.findElement(By.id("do_transaction")).click();
        assertElementPresent(By.id("meldung_207"));

        // check new balance
        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 55.000,00 €");
        Assertions.assertEquals("95000", driver.findElement(By.id("betrag")).getAttribute("value"));
        assertText(By.id("stat_money"), "95.000,00 €");
        assertText(By.id("stat_bank"), "55.000,00 €");
    }

    @Test
    void testDepositMax() {
        resetPlayer(13);
        WebDriver driver = getDriver();
        login(driver, "test3");
        driver.findElement(By.id("link_bank")).click();

        setValue(By.id("betrag"), "50000");
        driver.findElement(By.id("do_transaction")).click();
        assertElementPresent(By.id("meldung_207"));

        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 100.000,00 €");
        Assertions.assertEquals("50000", driver.findElement(By.id("betrag")).getAttribute("value"));
        assertText(By.id("stat_money"), "50.000,00 €");
        assertText(By.id("stat_bank"), "100.000,00 €");
    }

    @Test
    void testDepositNegative() {
        resetPlayer(13);
        WebDriver driver = getDriver();
        login(driver, "test3");
        driver.findElement(By.id("link_bank")).click();

        setValue(By.id("betrag"), "-100");
        driver.findElement(By.id("do_transaction")).click();
        assertElementNotPresent(By.id("meldung_207"));

        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 50.000,00 €");
        Assertions.assertEquals("-100", driver.findElement(By.id("betrag")).getAttribute("value"));
        assertText(By.id("stat_money"), "100.000,00 €");
        assertText(By.id("stat_bank"), "50.000,00 €");
    }

    @Test
    void testDepositTooMuch() {
        resetPlayer(13);
        WebDriver driver = getDriver();
        login(driver, "test3");
        driver.findElement(By.id("link_bank")).click();

        setValue(By.id("betrag"), "50000,01");
        driver.findElement(By.id("do_transaction")).click();
        assertElementPresent(By.id("meldung_110"));

        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 50.000,00 €");
        Assertions.assertEquals("50000.01", driver.findElement(By.id("betrag")).getAttribute("value"));
        assertText(By.id("stat_money"), "100.000,00 €");
        assertText(By.id("stat_bank"), "50.000,00 €");
    }

    @Test
    void testDepositZero() {
        resetPlayer(13);
        WebDriver driver = getDriver();
        login(driver, "test3");
        driver.findElement(By.id("link_bank")).click();

        // try to deposit -100
        setValue(By.id("betrag"), "0");
        driver.findElement(By.id("do_transaction")).click();
        assertElementPresent(By.id("meldung_110"));

        // check new balance
        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 50.000,00 €");
        assertText(By.id("stat_money"), "100.000,00 €");
        assertText(By.id("stat_bank"), "50.000,00 €");
    }

    @Test
    void testWithdraw() {
        resetPlayer(13);
        WebDriver driver = getDriver();
        login(driver, "test3");
        driver.findElement(By.id("link_bank")).click();

        // try to deposit -100
        select(By.id("art"), "Auszahlen");
        setValue(By.id("betrag"), "5000");
        driver.findElement(By.id("do_transaction")).click();
        assertElementPresent(By.id("meldung_207"));

        // check new balance
        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 45.000,00 €");
        assertText(By.id("stat_money"), "105.000,00 €");
        assertText(By.id("stat_bank"), "45.000,00 €");
    }

    @Test
    void testWithdrawCredit() {
        resetPlayer(13);
        WebDriver driver = getDriver();
        login(driver, "test3");
        driver.findElement(By.id("link_bank")).click();

        // try to deposit -100
        select(By.id("art"), "Auszahlen");
        setValue(By.id("betrag"), "65000");
        driver.findElement(By.id("do_transaction")).click();
        assertElementPresent(By.id("meldung_207"));

        // check new balance
        assertText(By.id("cur_bank_account"), "Ihr Kontostand: -15.000,00 €");
        assertText(By.id("stat_money"), "165.000,00 €");
        assertText(By.id("stat_bank"), "-15.000,00 €");
    }

    @Test
    void testWithdrawCreditLimit() {
        resetPlayer(13);
        WebDriver driver = getDriver();
        login(driver, "test3");
        driver.findElement(By.id("link_bank")).click();

        // try to deposit -100
        select(By.id("art"), "Auszahlen");
        setValue(By.id("betrag"), "65000.01");
        driver.findElement(By.id("do_transaction")).click();
        assertElementPresent(By.id("meldung_109"));

        // check new balance
        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 50.000,00 €");
        Assertions.assertEquals("65000.01", driver.findElement(By.id("betrag")).getAttribute("value"));
        assertText(By.id("stat_money"), "100.000,00 €");
        assertText(By.id("stat_bank"), "50.000,00 €");
    }

    @Test
    void testWithdrawNegative() {
        resetPlayer(13);
        WebDriver driver = getDriver();
        login(driver, "test3");
        driver.findElement(By.id("link_bank")).click();

        // try to deposit -100
        select(By.id("art"), "Auszahlen");
        setValue(By.id("betrag"), "-100");
        driver.findElement(By.id("do_transaction")).click();
        assertElementNotPresent(By.id("meldung_207"));

        // check new balance
        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 50.000,00 €");
        Assertions.assertEquals("-100", driver.findElement(By.id("betrag")).getAttribute("value"));
        assertText(By.id("stat_money"), "100.000,00 €");
        assertText(By.id("stat_bank"), "50.000,00 €");
    }

    @Test
    void testWithdrawZero() {
        resetPlayer(13);
        WebDriver driver = getDriver();
        login(driver, "test3");
        driver.findElement(By.id("link_bank")).click();

        // try to deposit -100
        select(By.id("art"), "Auszahlen");
        setValue(By.id("betrag"), "0");
        driver.findElement(By.id("do_transaction")).click();
        assertElementPresent(By.id("meldung_110"));

        // check new balance
        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 50.000,00 €");
        assertText(By.id("stat_money"), "100.000,00 €");
        assertText(By.id("stat_bank"), "50.000,00 €");
    }
}
