/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
package eu.fraho.blm2.st;

import org.junit.jupiter.api.Assertions;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;

import java.util.concurrent.ThreadLocalRandom;

public class BankTests extends AbstractTest {
    private static final int USER_ID = ThreadLocalRandom.current().nextInt(1_000_000);

    @BeforeEach
    void beforeEach() {
        resetPlayer(USER_ID, getClass().getSimpleName());
        login("test" + USER_ID);
    }

    @Test
    void testDeposit() {
        WebDriver driver = getDriver();
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
        WebDriver driver = getDriver();
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
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_bank")).click();

        setValue(By.id("betrag"), "-100");
        driver.findElement(By.id("do_transaction")).click();

        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 50.000,00 €");
        Assertions.assertEquals("-100", driver.findElement(By.id("betrag")).getAttribute("value"));
        assertText(By.id("stat_money"), "100.000,00 €");
        assertText(By.id("stat_bank"), "50.000,00 €");
    }

    @Test
    void testDepositTooMuch() {
        WebDriver driver = getDriver();
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
        WebDriver driver = getDriver();
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
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_bank")).click();

        // try to deposit -100
        driver.findElement(By.id("auszahlen")).click();
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
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_bank")).click();

        // try to deposit -100
        driver.findElement(By.id("auszahlen")).click();
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
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_bank")).click();

        // try to deposit -100
        driver.findElement(By.id("auszahlen")).click();
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
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_bank")).click();

        // try to deposit -100
        driver.findElement(By.id("auszahlen")).click();
        setValue(By.id("betrag"), "-100");
        driver.findElement(By.id("do_transaction")).click();

        // check new balance
        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 50.000,00 €");
        Assertions.assertEquals("-100", driver.findElement(By.id("betrag")).getAttribute("value"));
        assertText(By.id("stat_money"), "100.000,00 €");
        assertText(By.id("stat_bank"), "50.000,00 €");
    }

    @Test
    void testWithdrawZero() {
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_bank")).click();

        // try to deposit -100
        driver.findElement(By.id("einzahlen")).click();
        setValue(By.id("betrag"), "0");
        driver.findElement(By.id("do_transaction")).click();
        assertElementPresent(By.id("meldung_110"));

        // check new balance
        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 50.000,00 €");
        assertText(By.id("stat_money"), "100.000,00 €");
        assertText(By.id("stat_bank"), "50.000,00 €");
    }
}
