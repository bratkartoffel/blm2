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
import org.junit.jupiter.api.TestInfo;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;

import java.math.BigDecimal;

public class BankTests extends AbstractTest {
    private final int userId = getNextUserId();

    @BeforeEach
    void beforeEach(TestInfo testInfo) {
        resetPlayer(userId, testInfo);
        login("test" + userId);
    }

    @Test
    void testDeposit() {
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_bank")).click();

        // check current balance
        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 50,000.00 €");

        // deposit 5k
        setValue(By.id("betrag"), "5000");
        driver.findElement(By.id("do_transaction")).click();
        assertElementPresent(By.id("meldung_207"));

        // check new balance
        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 55,000.00 €");
        assertValue(By.id("betrag"), new BigDecimal("45000.00"));
        assertText(By.id("stat_money"), "95,000.00 €");
        assertText(By.id("stat_bank"), "55,000.00 €");
    }

    @Test
    void testDepositMax() {
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_bank")).click();

        setValue(By.id("betrag"), "50000");
        driver.findElement(By.id("do_transaction")).click();
        assertElementPresent(By.id("meldung_207"));

        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 100,000.00 €");
        assertValue(By.id("betrag"), new BigDecimal("0.00"));
        assertText(By.id("stat_money"), "50,000.00 €");
        assertText(By.id("stat_bank"), "100,000.00 €");
    }

    @Test
    void testDepositNegative() {
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_bank")).click();

        setValue(By.id("betrag"), "-100");
        driver.findElement(By.id("do_transaction")).click();

        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 50,000.00 €");
        assertValue(By.id("betrag"), new BigDecimal("-100"));
        assertText(By.id("stat_money"), "100,000.00 €");
        assertText(By.id("stat_bank"), "50,000.00 €");
    }

    @Test
    void testDepositTooMuch() {
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_bank")).click();

        setValue(By.id("betrag"), "50000,01");
        driver.findElement(By.id("do_transaction")).click();
        assertElementPresent(By.id("meldung_110"));

        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 50,000.00 €");
        assertValue(By.id("betrag"), new BigDecimal("50000.01"));
        assertText(By.id("stat_money"), "100,000.00 €");
        assertText(By.id("stat_bank"), "50,000.00 €");
    }

    @Test
    void testDepositZero() {
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_bank")).click();

        setValue(By.id("betrag"), "0");
        driver.findElement(By.id("do_transaction")).click();
        assertElementPresent(By.id("meldung_110"));

        // check new balance
        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 50,000.00 €");
        assertText(By.id("stat_money"), "100,000.00 €");
        assertText(By.id("stat_bank"), "50,000.00 €");
    }

    @Test
    void testWithdraw() {
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_bank")).click();

        driver.findElement(By.id("auszahlen")).click();
        setValue(By.id("betrag"), "5000");
        driver.findElement(By.id("do_transaction")).click();
        assertElementPresent(By.id("meldung_207"));

        // check new balance
        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 45,000.00 €");
        assertText(By.id("stat_money"), "105,000.00 €");
        assertText(By.id("stat_bank"), "45,000.00 €");
    }

    @Test
    void testWithdrawCredit() {
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_bank")).click();

        driver.findElement(By.id("auszahlen")).click();
        setValue(By.id("betrag"), "65000");
        driver.findElement(By.id("do_transaction")).click();
        assertElementPresent(By.id("meldung_207"));

        // check new balance
        assertText(By.id("cur_bank_account"), "Ihr Kontostand: -15,000.00 €");
        assertText(By.id("stat_money"), "165,000.00 €");
        assertText(By.id("stat_bank"), "-15,000.00 €");
    }

    @Test
    void testWithdrawCreditLimit() {
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_bank")).click();

        driver.findElement(By.id("auszahlen")).click();
        setValue(By.id("betrag"), "65000.01");
        driver.findElement(By.id("do_transaction")).click();
        assertElementPresent(By.id("meldung_109"));

        // check new balance
        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 50,000.00 €");
        assertValue(By.id("betrag"), new BigDecimal("65000.01"));
        assertText(By.id("stat_money"), "100,000.00 €");
        assertText(By.id("stat_bank"), "50,000.00 €");
    }

    @Test
    void testWithdrawNegative() {
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_bank")).click();

        driver.findElement(By.id("auszahlen")).click();
        setValue(By.id("betrag"), "-100");
        driver.findElement(By.id("do_transaction")).click();

        // check new balance
        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 50,000.00 €");
        assertValue(By.id("betrag"), new BigDecimal("-100"));
        assertText(By.id("stat_money"), "100,000.00 €");
        assertText(By.id("stat_bank"), "50,000.00 €");
    }

    @Test
    void testWithdrawZero() {
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_bank")).click();

        driver.findElement(By.id("einzahlen")).click();
        setValue(By.id("betrag"), "0");
        driver.findElement(By.id("do_transaction")).click();
        assertElementPresent(By.id("meldung_110"));

        // check new balance
        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 50,000.00 €");
        assertText(By.id("stat_money"), "100,000.00 €");
        assertText(By.id("stat_bank"), "50,000.00 €");
    }

    @Test
    void testTextFieldPreFilled() {
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_bank")).click();

        driver.findElement(By.id("einzahlen")).click();
        assertValue(By.id("betrag"), new BigDecimal("99876.99"));

        driver.findElement(By.id("auszahlen")).click();
        assertValue(By.id("betrag"), new BigDecimal("123.01"));

        driver.findElement(By.id("gruppen_kasse")).click();
        assertValue(By.id("betrag"), new BigDecimal("100001"));

        // manually change the value, it shouldn't update automatically now
        setValue(By.id("betrag"), "100");

        driver.findElement(By.id("einzahlen")).click();
        assertValue(By.id("betrag"), new BigDecimal("100"));

        driver.findElement(By.id("auszahlen")).click();
        assertValue(By.id("betrag"), new BigDecimal("100"));

        driver.findElement(By.id("gruppen_kasse")).click();
        assertValue(By.id("betrag"), new BigDecimal("100"));
    }

    @Test
    void testDepositWithBankSafe() {
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_bank")).click();

        driver.findElement(By.id("einzahlen")).click();
        assertValue(By.id("betrag"), new BigDecimal("130000.00"));

        driver.findElement(By.id("do_transaction")).click();
        assertElementPresent(By.id("meldung_207"));

        assertText(By.id("cur_bank_account"), "Ihr Kontostand: 200,000.00 €");
        assertValue(By.id("betrag"), new BigDecimal("0.00"));
        assertText(By.id("stat_money"), "70,000.00 €");
        assertText(By.id("stat_bank"), "200,000.00 €");
    }

    @Test
    void testInterestPlusWithCron() {
        runCronjob();

        WebDriver driver = getDriver();
        driver.findElement(By.id("link_buero")).click();
        assertText(By.id("b_i_3"), "660.00 €");

        assertText(By.id("stat_bank"), "50,660.00 €");
    }

    @Test
    void testInterestPlusWithCronAndBuilding() {
        runCronjob();

        WebDriver driver = getDriver();
        driver.findElement(By.id("link_buero")).click();
        assertText(By.id("b_i_3"), "1,318.68 €");

        assertText(By.id("stat_bank"), "101,218.68 €");
    }

    @Test
    void testInterestPlusLimitWithCron() {
        runCronjob();
        runCronjob();

        WebDriver driver = getDriver();
        driver.findElement(By.id("link_buero")).click();
        assertText(By.id("b_i_3"), "100.00 €");
        assertText(By.id("stat_bank"), "100,000.00 €");
    }

    @Test
    void testResetAfterDispoLimit() {
        runCronjob();

        WebDriver driver = getDriver();
        driver.findElement(By.id("link_nachrichten_liste")).click();
        assertText(By.id("stat_money"), "5,000.00 €");
        Assertions.assertEquals("1", driver.findElement(By.id("MessagesIn")).getAttribute("data-count"));
    }
}
