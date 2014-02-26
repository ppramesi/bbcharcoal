<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template match="/">
<wsdl2php>
    <classes>
        <xsl:apply-templates select="//*[local-name()='types' and namespace-uri()='http://schemas.xmlsoap.org/wsdl/']" />
    </classes>
    <services>
        <xsl:apply-templates select="//*[local-name()='service' and namespace-uri()='http://schemas.xmlsoap.org/wsdl/']" />
    </services>
</wsdl2php>
</xsl:template>

<xsl:template match="*[local-name()='types']">
    <xsl:apply-templates select=".//*[local-name()='complexType' and not(starts-with(@name, 'ArrayOf_'))]" />
</xsl:template>

<xsl:template match="*[local-name()='complexType']">
    <class name="{@name | ../@name}">
        <xsl:if test=".//*[local-name()='extension']">
            <extends>
                <xsl:value-of select="substring-after(.//*[local-name()='extension']/@base,':')" />
            </extends>
        </xsl:if>
        <xsl:if test=".//*[local-name()='element']">
            <properties>
                <xsl:for-each select=".//*[local-name()='element']">
                    <xsl:choose>
                        <xsl:when test="substring-before(@type,':')='xsd'">
                            <property name="{@name}" type="{substring-after(@type,':')}" />
                        </xsl:when>
                        <xsl:when test="//*[local-name()='complexType' and @name=substring-after(current()/@type,':')]//*[@ref='soapenc:arrayType']">
                            <xsl:variable name="type"
                                select="//*[local-name()='complexType' and @name=substring-after(current()/@type,':')]//*[@ref='soapenc:arrayType']/@*[local-name()='wsdl:arrayType']" />
                            <property name="{@name}" type="{substring-after($type,':')}" />
                        </xsl:when>
                        <xsl:when test="not(@type) and @ref">
                            <xsl:choose>
                                <xsl:when test="@name">
                                    <property name="{@name}" type="{substring-after(@ref,':')}" />
                                </xsl:when>
                                <xsl:otherwise>
                                    <property name="{substring-after(@ref,':')}" type="{substring-after(@ref,':')}" />
                                </xsl:otherwise>
                            </xsl:choose>
                        </xsl:when>
                        <xsl:otherwise>
                            <property name="{@name}" type="{@type}" />
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:for-each>
            </properties>
        </xsl:if>
    </class>
</xsl:template>

<xsl:template match="*[local-name()='service']">
    <service name="{@name}">
        <xsl:apply-templates select="*[local-name()='port' and namespace-uri()='http://schemas.xmlsoap.org/wsdl/']" />
    </service>
</xsl:template>

<xsl:template match="*[local-name()='port']">
    <xsl:apply-templates select="//*[local-name()='binding' and @name=substring-after(current()/@binding,':')]" />
</xsl:template>

<xsl:template match="*[local-name()='binding']">
    <functions>
        <xsl:apply-templates select=".//*[local-name()='operation' and namespace-uri()='http://schemas.xmlsoap.org/wsdl/']" />
    </functions>
</xsl:template>

<xsl:template match="*[local-name()='operation' and namespace-uri()='http://schemas.xmlsoap.org/wsdl/']">
    <function name="{@name}">
        <parameters>
            <xsl:apply-templates select="//*[local-name()='message' and @name=current()/*[local-name()='input']/@name]" />
        </parameters>
        <returns>
            <xsl:apply-templates select="//*[local-name()='message' and @name=current()/*[local-name()='output']/@name]" />
        </returns>
    </function>
</xsl:template>

<xsl:template match="*[local-name()='message']">
    <xsl:for-each select="*[local-name()='part']">
        <xsl:choose>
            <xsl:when test="substring-before(@type,':')='xsd'">
                <variable name="{@name}" type="{substring-after(@type,':')}" />
            </xsl:when>
            <xsl:when test="//*[local-name()='complexType' and @name=substring-after(current()/@type,':')]//*[@ref='soapenc:arrayType']">
                <xsl:variable name="type"
                    select="//*[local-name()='complexType' and @name=substring-after(current()/@type,':')]//*[@ref='soapenc:arrayType']/@*[local-name()='arrayType']" />
                <variable name="{@name}" type="{substring-after($type,':')}" />
            </xsl:when>
            <xsl:when test="not(@type) and @element">
                <xsl:choose>
                    <xsl:when test="@name">
                        <variable name="{@name}" type="{substring-after(@element,':')}" />
                    </xsl:when>
                    <xsl:otherwise>
                        <variable name="{substring-after(@element,':')}" type="{substring-after(@element,':')}" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:otherwise>
                <variable name="{@name}" type="{@type}" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:for-each>
</xsl:template>

</xsl:stylesheet>
