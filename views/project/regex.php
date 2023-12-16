<?php
$this->title = 'pControl|Regex';
use yii\helpers\Html;
?>
<h4 class="text-primary text-center">راهنمای استفاده از عبارات باقاعده برای جستجو</h4>

<p class="backicon">
    <a href="index"><i class="fa fa-chevron-left fa-2x" style="color:white;position: relative;"></i></a>
</p>

<div style="display: flex; align-items:center; flex-wrap:wrap; justify-content: center;">

    <a href="#charDiv" style="margin-left:5px;text-align:center;">
        <div class=" bg-menu menu-box  margin-5 padding-5">
            <p style="color:darkgoldenrod;">Char</p>
        </div>
    </a>
    <a href="#quntyDiv" style="margin-left:5px;text-align:center;">
        <div class=" bg-menu menu-box  margin-5 padding-5">
            <p style="color:darkmagenta;">Qnty</p>
        </div>
    </a>
    <a href="#assertDiv" style="margin-left:5px;text-align:center;">
        <div class=" bg-menu menu-box  margin-5 padding-5">
            <p style="color:#fd7e14;">Assert</p>
        </div>
    </a>
    <a href="#wildDiv" style="margin-left:5px;text-align:center;">
        <div class=" bg-menu menu-box  margin-5 padding-5">
            <p style="color:purple;">Wildcard</p>
        </div>
    </a>
    <a href="#examDiv" style="margin-left:5px;text-align:center;">
        <div class=" bg-menu menu-box  margin-5 padding-5">
            <p style="color:slateblue;">Example</p>
        </div>
    </a>
</div>

<br />
<div id="charDiv" style="margin: 10px auto; padding:10px;">
    <h4 class="text-primary text-center" style="color:darkgoldenrod;">Characters and Abbreviations for Sets of Characters</h4>
    <table class="table table-responsive table-hover table-striped" >
        <thead>
        <tr>
            <th>Element</th>
            <th>Meaning</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>c</td>
            <td>A character represents itself unless it has a special regexp meaning. e.g. c matches the character c.</td>
        </tr>
        <tr>
            <td>\c</td>
            <td>A character that follows a backslash matches the character itself, except as specified below. e.g., To match a literal caret at the beginning of a string, write \^.</td>
        </tr>
        <tr>
            <td>\a</td>
            <td>Matches the ASCII bell (BEL, 0x07).</td>
        </tr>
        <tr>
            <td>\f</td>
            <td>Matches the ASCII form feed (FF, 0x0C).</td>
        </tr>
        <tr>
            <td>\n</td>
            <td>Matches the ASCII line feed (LF, 0x0A, Unix newline).</td>
        </tr>
        <tr>
            <td>\r</td>
            <td>Matches the ASCII carriage return (CR, 0x0D).</td>
        </tr>
        <tr>
            <td>\t</td>
            <td>Matches the ASCII horizontal tab (HT, 0x09).</td>
        </tr>
        <tr>
            <td>\v</td>
            <td>Matches the ASCII vertical tab (VT, 0x0B).</td>
        </tr>
        <tr>
            <td>\xhhhh</td>
            <td>Matches the Unicode character corresponding to the hexadecimal number hhhh (between 0x0000 and 0xFFFF).</td>
        </tr>
        <tr>
            <td>\0ooo (i.e., \zero ooo)</td>
            <td>matches the ASCII/Latin1 character for the octal number ooo (between 0 and 0377).</td>
        </tr>
        <tr>
            <td>. (dot)</td>
            <td>Matches any character (including newline).</td>
        </tr>
        <tr>
            <td>\d</td>
            <td>Matches a digit.</td>
        </tr>
        <tr>
            <td>\D</td>
            <td>Matches a non-digit.</td>
        </tr>
        <tr>
            <td>\s</td>
            <td>Matches a whitespace character .</td>
        </tr>
        <tr>
            <td>\S</td>
            <td>Matches a non-whitespace character.</td>
        </tr>
        <tr>
            <td>\w</td>
            <td>Matches a word character.</td>
        </tr>
        <tr>
            <td>\W</td>
            <td>Matches a non-word character.</td>
        </tr>
        <tr>
            <td>\n</td>
            <td>The n-th backreference, e.g. \1, \2, etc.</td>
        </tr>
        </tbody>
    </table>
</div>
<hr />
<div id="quntyDiv" style="margin: 10px auto; padding:10px;">
    <h4 class="text-primary text-center" style="color:darkmagenta;">Quantifiers</h4>
    <p>By default, an expression is automatically quantified by {1,1}, i.e. it should occur exactly once. In the following list, E stands for expression. An expression is a character, or an abbreviation for a set of characters, or a set of characters in square brackets, or an expression in parentheses.</p>
    <table class="table table-responsive table-hover table-striped" >
        <thead>
        <tr>
            <th>Element</th>
            <th>Meaning</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>E?</td>
            <td>Matches zero or one occurrences of E. This quantifier means The previous expression is optional, because it will match whether or not the expression is found. E? is the same as E{0,1}. e.g., dents? matches 'dent' or 'dents'.</td>
        </tr>
        <tr>
            <td>E+</td>
            <td>Matches one or more occurrences of E. E+ is the same as E{1,}. e.g., 0+ matches '0', '00', '000', etc.</td>
        </tr>
        <tr>
            <td>E*</td>
            <td>Matches zero or more occurrences of E. It is the same as E{0,}. The * quantifier is often used in error where + should be used. For example, if \s*$ is used in an expression to match strings that end in whitespace, it will match every string because \s*$ means Match zero or more whitespaces followed by end of string. The correct regexp to match strings that have at least one trailing whitespace character is \s+$.</td>
        </tr>
        <tr>
            <td>E{n}</td>
            <td>Matches exactly n occurrences of E. E{n} is the same as repeating E n times. For example, x{5} is the same as xxxxx. It is also the same as E{n,n}, e.g. x{5,5}.</td>
        </tr>
        <tr>
            <td>E{n,}</td>
            <td>Matches at least n occurrences of E.</td>
        </tr>
        <tr>
            <td>E{,m}</td>
            <td>Matches at most m occurrences of E. E{,m} is the same as E{0,m}.</td>
        </tr>
        <tr>
            <td>E{n,m}</td>
            <td>Matches at least n and at most m occurrences of E.</td>
        </tr>
        </tbody>
    </table>
</div>
<hr />
<div id="assertDiv" style="margin: 10px auto; padding:10px;">
    <h4 class="text-primary text-center" style="color:#fd7e14;">Assertions</h4>
    <p>Assertions make some statement about the text at the point where they occur in the regexp but they do not match any characters. In the following list E stands for any expression.</p>
    <table class="table table-responsive table-hover table-striped" >
        <thead>
        <tr>
            <th>Element</th>
            <th>Meaning</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>^</td>
            <td>The caret signifies the beginning of the string. If you wish to match a literal ^ you must escape it by writing \\^. For example, ^#include will only match strings which begin with the characters '#include'. </td>
        </tr>
        <tr>
            <td>$</td>
            <td>The dollar signifies the end of the string. For example \d\s*$ will match strings which end with a digit optionally followed by whitespace. If you wish to match a literal $ you must escape it by writing \\$.</td>
        </tr>
        <tr>
            <td>\b</td>
            <td>A word boundary. For example the regexp \bOK\b means match immediately after a word boundary (e.g. start of string or whitespace) the letter 'O' then the letter 'K' immediately before another word boundary (e.g. end of string or whitespace). But note that the assertion does not actually match any whitespace so if we write (\bOK\b) and we have a match it will only contain 'OK' even if the string is "It's OK now".</td>
        </tr>
        <tr>
            <td>\B</td>
            <td>A non-word boundary. This assertion is true wherever \b is false. For example if we searched for \Bon\B in "Left on" the match would fail (space and end of string aren't non-word boundaries), but it would match in "tonne".</td>
        </tr>
        <tr>
            <td>(?=E)</td>
            <td>Positive lookahead. This assertion is true if the expression matches at this point in the regexp. For example, const(?=\s+char) matches 'const' whenever it is followed by 'char', as in 'static const char *'. </td>
        </tr>
        <tr>
            <td>(?!E)</td>
            <td>Negative lookahead. This assertion is true if the expression does not match at this point in the regexp. For example, const(?!\s+char) matches 'const' except when it is followed by 'char'.</td>
        </tr>
        </tbody>
    </table>
</div>
<hr />
<div id="wildDiv" style="margin: 10px auto; padding:10px;">
    <h4 class="text-primary text-center" style="color:purple;">Wildcard Matching</h4>
    <p>Most command shells such as bash or cmd.exe support "file globbing", the ability to identify a group of files by using wildcards. </p>
    <table class="table table-responsive table-hover table-striped" >
        <thead>
        <tr>
            <th>Element</th>
            <th>Meaning</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>c</td>
            <td>Any character represents itself apart from those mentioned below. Thus c matches the character c.</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>?</td>
            <td>Matches any single character. It is the same as . in full regexps.</td>
        </tr>
        <tr>
            <td>*</td>
            <td>Matches zero or more of any characters. It is the same as .* in full regexps.</td>
        </tr>
        <tr>
            <td>[...]</td>
            <td>Sets of characters can be represented in square brackets, similar to full regexps. Within the character class, like outside, backslash has no special meaning.</td>
        </tr>
        </tbody>
    </table>
</div>
<hr />
<div id="examDiv" style="margin:10px auto; padding:10px;">
    <h4 class="text-primary text-center" style="color:slateblue;">Examples </h4>
    <table class="table table-responsive table-hover table-striped" >
        <thead>
        <tr>
            <th>Regex</th>
            <th>Meaning</th>
        </tr>
        </thead>
        <tbody>
            <tr>
                <td>^2/</td>
                <td>anything starts with 2/</td>
            </tr>
            <tr>
                <td>/2$</td>
                <td>anything ends by /2</td>
            </tr>
            <tr>
                <td>^2/.*/0$</td>
                <td>anything starts with 2/ and ends by /0</td>
            </tr>
            <tr>
                <td>^[2,5]/0</td>
                <td>anything starts with either 2/0 or 5/0</td>
            </tr>
            <tr>
                <td>^2/0/[4-6]$</td>
                <td>anything starts with 2/0/ and ends by 4, 5 or 6</td>
            </tr>
            <tr>
                <td>^[2,3]</td>
                <td>anything starts with either 2 or 3</td>
            </tr>
            <tr>
                <td>^[2-5]</td>
                <td>anything starts with 2, 3, 4 or 5</td>
            </tr>
            <tr>
                <td>^2[0,2]$</td>
                <td>anything starts with 2 and ends by either 0 or 2</td>
            </tr>
            <tr>
                <td>^[3,4][2,3]$</td>
                <td>anything starts with either 3 or 4 and ends by either 2 or 3</td>
            </tr>
            <tr>
                <td>^(90|11)</td>
                <td>anything starts with either 90 or 11</td>
            </tr>
            <tr>
                <td>^(?!AGG)</td>
                <td>anything not starts with AGG</td>
            </tr>
            <tr>
                <td>^\s*$  OR  ^ *$</td>
                <td>matches space(empty)</td>
            </tr>
            <tr>
                <td>^\d+:\d+</td>
                <td>matches N1:N2 like 65021:3000</td>
            </tr>
            <tr>
                <td>^\d+:\d+(\s*\d+:\d+)*</td>
                <td>matches one or more space separated rd like [65021:10 65021:100]</td>
            </tr>
        </tbody>
    </table>
</div>